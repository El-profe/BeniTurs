<?php
namespace App\Services;

use App\Core\Database;
use PDO;
use PDOException;

/**
 * Registra visitas públicas anónimas y provee analítica detallada de tráfico turístico:
 * por hora, por día, por semana y por mes.
 */
class AnaliticaVisitasService {
    private const COOKIE_VISITANTE = 'beniturs_visitante';

    public static function registrarVisita(): void {
        $token = $_COOKIE[self::COOKIE_VISITANTE] ?? '';
        if (!is_string($token) || !preg_match('/^[a-f0-9]{64}$/', $token)) {
            $token = bin2hex(random_bytes(32));
            setcookie(self::COOKIE_VISITANTE, $token, [
                'expires'  => time() + 31536000,
                'path'     => '/',
                'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }

        try {
            $stmt = Database::getConnection()->prepare(
                'INSERT IGNORE INTO visitas_diarias (fecha, visitante_hash) VALUES (CURDATE(), :visitante_hash)'
            );
            $stmt->execute([':visitante_hash' => hash('sha256', $token)]);
        } catch (PDOException $e) {
            error_log('Analitica de visitas no disponible: ' . $e->getMessage());
        }
    }

    /**
     * Resumen numérico rápido de visitantes
     */
    public static function resumen(): array {
        try {
            $db = Database::getConnection();
            $hoy = (int)$db->query('SELECT COUNT(*) FROM visitas_diarias WHERE fecha = CURDATE()')->fetchColumn();
            $semana = (int)$db->query('SELECT COUNT(*) FROM visitas_diarias WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)')->fetchColumn();
            $mes = (int)$db->query("SELECT COUNT(*) FROM visitas_diarias WHERE fecha >= DATE_FORMAT(CURDATE(), '%Y-%m-01')")->fetchColumn();
            $total = (int)$db->query('SELECT COUNT(*) FROM visitas_diarias')->fetchColumn();

            return [
                'hoy'    => $hoy,
                'semana' => $semana,
                'mes'    => $mes,
                'total'  => $total
            ];
        } catch (PDOException $e) {
            return ['hoy' => 0, 'semana' => 0, 'mes' => 0, 'total' => 0];
        }
    }

    /**
     * Obtiene el desglose analítico completo: por horas, por días, por semanas y por meses
     */
    public static function obtenerAnaliticaCompleta(): array {
        $db = Database::getConnection();

        // 1. Resumen general y picos
        $resumen = self::resumen();

        // 2. Desglose Por Horas (00:00 a 23:00)
        $horas = [];
        for ($h = 0; $h < 24; $h++) {
            $clave = sprintf('%02d:00', $h);
            $horas[$clave] = 0;
        }

        $horaPico = 'N/D';
        $maxVisitasHora = -1;

        try {
            $stmtH = $db->query("SELECT HOUR(created_at) AS hora, COUNT(*) AS total FROM visitas_diarias GROUP BY hora ORDER BY hora ASC");
            while ($r = $stmtH->fetch(PDO::FETCH_ASSOC)) {
                $clave = sprintf('%02d:00', (int)$r['hora']);
                $cant = (int)$r['total'];
                $horas[$clave] = $cant;
                if ($cant > $maxVisitasHora) {
                    $maxVisitasHora = $cant;
                    $horaFin = sprintf('%02d:00', ((int)$r['hora'] + 1) % 24);
                    $horaPico = "{$clave} - {$horaFin} ({$cant} visitas)";
                }
            }
        } catch (PDOException $e) {}

        // 3. Desglose Por Días (Últimos 14 días)
        $nombresDiasEs = [
            'Mon' => 'Lun', 'Tue' => 'Mar', 'Wed' => 'Mié', 
            'Thu' => 'Jue', 'Fri' => 'Vie', 'Sat' => 'Sáb', 'Sun' => 'Dom'
        ];
        $diasSemanaConteo = ['Lun' => 0, 'Mar' => 0, 'Mié' => 0, 'Jue' => 0, 'Vie' => 0, 'Sáb' => 0, 'Dom' => 0];

        $dias = [];
        for ($i = 13; $i >= 0; $i--) {
            $fecha = date('Y-m-d', strtotime("-$i days"));
            $diaIngles = date('D', strtotime($fecha));
            $diaEsp = $nombresDiasEs[$diaIngles] ?? $diaIngles;
            $dias[$fecha] = [
                'fecha'      => $fecha,
                'label'      => date('d/m', strtotime($fecha)),
                'dia_semana' => $diaEsp,
                'total'      => 0
            ];
        }

        try {
            $stmtD = $db->query("SELECT fecha, COUNT(*) AS total FROM visitas_diarias WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 14 DAY) GROUP BY fecha ORDER BY fecha ASC");
            while ($r = $stmtD->fetch(PDO::FETCH_ASSOC)) {
                if (isset($dias[$r['fecha']])) {
                    $dias[$r['fecha']]['total'] = (int)$r['total'];
                }
            }

            // Día de la semana más concurrido históricamente
            $stmtDS = $db->query("SELECT DATE_FORMAT(fecha, '%a') as dia_en, COUNT(*) as total FROM visitas_diarias GROUP BY dia_en");
            while ($r = $stmtDS->fetch(PDO::FETCH_ASSOC)) {
                $esp = $nombresDiasEs[$r['dia_en']] ?? $r['dia_en'];
                if (isset($diasSemanaConteo[$esp])) {
                    $diasSemanaConteo[$esp] += (int)$r['total'];
                }
            }
        } catch (PDOException $e) {}

        $diaPico = 'N/D';
        arsort($diasSemanaConteo);
        $topDia = array_key_first($diasSemanaConteo);
        if ($topDia && $diasSemanaConteo[$topDia] > 0) {
            $diaPico = "{$topDia} ({$diasSemanaConteo[$topDia]} visitas acumuladas)";
        }

        // 4. Desglose Por Semanas (Últimas 8 semanas)
        $semanas = [];
        for ($i = 7; $i >= 0; $i--) {
            $lunesSemana = date('Y-m-d', strtotime("-$i weeks monday this week"));
            $domingoSemana = date('d/m', strtotime("{$lunesSemana} +6 days"));
            $claveSemana = date('oW', strtotime($lunesSemana)); // Año + semana ISO
            $semanas[$claveSemana] = [
                'label'        => 'Sem ' . date('W', strtotime($lunesSemana)) . ' (' . date('d/m', strtotime($lunesSemana)) . ')',
                'rango'        => date('d/m', strtotime($lunesSemana)) . ' al ' . $domingoSemana,
                'total'        => 0
            ];
        }

        try {
            $stmtS = $db->query("SELECT YEARWEEK(fecha, 1) as semana, COUNT(*) as total FROM visitas_diarias WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 8 WEEK) GROUP BY semana");
            while ($r = $stmtS->fetch(PDO::FETCH_ASSOC)) {
                if (isset($semanas[$r['semana']])) {
                    $semanas[$r['semana']]['total'] = (int)$r['total'];
                }
            }
        } catch (PDOException $e) {}

        // 5. Desglose Por Meses (Últimos 6 meses)
        $nombresMeses = ['01' => 'Ene', '02' => 'Feb', '03' => 'Mar', '04' => 'Abr', '05' => 'May', '06' => 'Jun', '07' => 'Jul', '08' => 'Ago', '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dic'];
        $meses = [];
        for ($i = 5; $i >= 0; $i--) {
            $fecha = strtotime("-$i months");
            $clave = date('Y-m', $fecha);
            $meses[$clave] = [
                'label' => $nombresMeses[date('m', $fecha)] . ' ' . date('y', $fecha),
                'total' => 0
            ];
        }

        try {
            $stmtM = $db->query("SELECT DATE_FORMAT(fecha, '%Y-%m') AS mes, COUNT(*) AS total FROM visitas_diarias GROUP BY mes ORDER BY mes ASC");
            while ($r = $stmtM->fetch(PDO::FETCH_ASSOC)) {
                if (isset($meses[$r['mes']])) {
                    $meses[$r['mes']]['total'] = (int)$r['total'];
                }
            }
        } catch (PDOException $e) {}

        return [
            'resumen'   => $resumen,
            'hora_pico' => $horaPico,
            'dia_pico'  => $diaPico,
            'horas'     => [
                'labels'  => array_keys($horas),
                'valores' => array_values($horas)
            ],
            'dias'      => [
                'labels'      => array_column($dias, 'label'),
                'dias_semana' => array_column($dias, 'dia_semana'),
                'valores'     => array_column($dias, 'total'),
                'detalles'    => array_values($dias)
            ],
            'semanas'   => [
                'labels'   => array_column($semanas, 'label'),
                'rangos'   => array_column($semanas, 'rango'),
                'valores'  => array_column($semanas, 'total'),
                'detalles' => array_values($semanas)
            ],
            'meses'     => [
                'labels'   => array_column($meses, 'label'),
                'valores'  => array_column($meses, 'total'),
                'detalles' => array_values($meses)
            ]
        ];
    }

    /**
     * Mantiene retrocompatibilidad con llamadas existentes
     */
    public static function ultimosSeisMeses(): array {
        $analitica = self::obtenerAnaliticaCompleta();
        return $analitica['meses'];
    }
}
