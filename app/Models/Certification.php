<?php

namespace App\Models;

use App\Core\Model;

/**
 * Certificaciones profesionales.
 */
class Certification extends Model
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function published(?int $limit = null): array
    {
        $sql = 'SELECT id, title, issuer, credential_id, credential_url, badge_image,
                       description, issued_on, expires_on
                  FROM certifications
                 WHERE is_published = 1
              ORDER BY sort_order ASC, issued_on DESC';

        $params = [];

        if ($limit !== null) {
            $sql     .= ' LIMIT ?';
            $params[] = $limit;
        }

        return $this->all($sql, $params);
    }

    public function countPublished(): int
    {
        $row = $this->one('SELECT COUNT(*) AS total FROM certifications WHERE is_published = 1');

        return (int) ($row['total'] ?? 0);
    }

    /**
     * Emisores distintos, para el resumen de la sección.
     *
     * @return array<int, string>
     */
    public function issuers(): array
    {
        $rows = $this->all(
            'SELECT DISTINCT issuer FROM certifications WHERE is_published = 1 ORDER BY issuer ASC'
        );

        return array_map(static fn (array $row): string => (string) $row['issuer'], $rows);
    }

    /**
     * Estado de vigencia de una credencial.
     */
    public static function status(?string $expiresOn): string
    {
        if (empty($expiresOn)) {
            return 'permanente';
        }

        return strtotime($expiresOn) >= time() ? 'vigente' : 'expirada';
    }
}
