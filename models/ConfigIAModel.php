<?php
// models/ConfigIAModel.php

namespace App\Models;

use App\Core\Model;
use PDO;

class ConfigIAModel extends Model {
    private $table = 'config_ia';

    public function get() {
        $query = "SELECT * FROM {$this->table} WHERE id = 1 LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result) {
            // Configuração padrão
            $default = [
                'id' => 1,
                'ativo' => 1,
                'portais' => json_encode(['pncp', 'comprasnet']),
                'palavras_chave' => '',
                'sound_alerts_enabled' => 1,
                'notification_sound' => 'ping',
                'daily_email_summary_enabled' => 1,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $this->save($default);
            return $default;
        }
        
        return $result;
    }

    public function save($data) {
        $data['portais'] = is_array($data['portais']) ? json_encode($data['portais']) : $data['portais'];
        
        $query = "INSERT INTO {$this->table} 
                  (id, ativo, portais, palavras_chave, sound_alerts_enabled, notification_sound, daily_email_summary_enabled, updated_at)
                  VALUES (1, :ativo, :portais, :palavras_chave, :sound_alerts_enabled, :notification_sound, :daily_email_summary_enabled, NOW())
                  ON DUPLICATE KEY UPDATE
                  ativo = VALUES(ativo),
                  portais = VALUES(portais),
                  palavras_chave = VALUES(palavras_chave),
                  sound_alerts_enabled = VALUES(sound_alerts_enabled),
                  notification_sound = VALUES(notification_sound),
                  daily_email_summary_enabled = VALUES(daily_email_summary_enabled),
                  updated_at = NOW()";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':ativo' => $data['ativo'] ?? 1,
            ':portais' => $data['portais'],
            ':palavras_chave' => $data['palavras_chave'] ?? '',
            ':sound_alerts_enabled' => $data['sound_alerts_enabled'] ?? 1,
            ':notification_sound' => $data['notification_sound'] ?? 'ping',
            ':daily_email_summary_enabled' => $data['daily_email_summary_enabled'] ?? 1
        ]);
    }

    public function getPortaisAtivos() {
        $config = $this->get();
        $portais = json_decode($config['portais'], true);
        return is_array($portais) ? $portais : [];
    }
}