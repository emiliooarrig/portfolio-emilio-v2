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
              ORDER BY id ASC';

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

    // --------------------------------------------------------
    //  Panel de administración — publicadas y ocultas
    // --------------------------------------------------------

    /**
     * @return array<int, array<string, mixed>>
     */
    public function adminList(): array
    {
        return $this->all(
            'SELECT id, title, issuer, credential_id, credential_url, badge_image,
                    description, issued_on, expires_on, is_published
               FROM certifications
           ORDER BY id ASC'
        );
    }

    // --------------------------------------------------------
    //  Panel: escritura
    // --------------------------------------------------------

    /**
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        return $this->one('SELECT * FROM certifications WHERE id = ? LIMIT 1', [$id]);
    }

    /**
     * @param  array<string, mixed> $input
     * @return array<string, string>
     */
    public function validate(array $input): array
    {
        $errors = [];

        $title = trim((string) ($input['title'] ?? ''));

        if ($title === '') {
            $errors['title'] = 'Escribe el nombre de la credencial.';
        } elseif (mb_strlen($title) > 180) {
            $errors['title'] = 'Máximo 180 caracteres.';
        }

        $issuer = trim((string) ($input['issuer'] ?? ''));

        if ($issuer === '') {
            $errors['issuer'] = 'Falta quién la emite.';
        } elseif (mb_strlen($issuer) > 120) {
            $errors['issuer'] = 'Máximo 120 caracteres.';
        }

        $issued = trim((string) ($input['issued_on'] ?? ''));

        if ($issued === '') {
            $errors['issued_on'] = 'La fecha de emisión es obligatoria.';
        } elseif (! $this->isDate($issued)) {
            $errors['issued_on'] = 'Fecha no válida.';
        }

        $expires = trim((string) ($input['expires_on'] ?? ''));

        if ($expires !== '' && ! $this->isDate($expires)) {
            $errors['expires_on'] = 'Fecha no válida.';
        } elseif ($expires !== '' && $issued !== '' && $errors === [] && $expires < $issued) {
            $errors['expires_on'] = 'No puede vencer antes de emitirse. Déjalo vacío si no vence.';
        }

        $url = trim((string) ($input['credential_url'] ?? ''));

        if ($url !== '' && ! $this->isUrl($url)) {
            $errors['credential_url'] = 'La dirección debe empezar por http:// o https://.';
        }

        if (mb_strlen(trim((string) ($input['description'] ?? ''))) > 400) {
            $errors['description'] = 'Máximo 400 caracteres.';
        }

        return $errors;
    }

    /**
     * @param array<string, mixed> $input
     */
    public function create(array $input): int
    {
        return $this->db()->execute(
            'INSERT INTO certifications
                (`title`, `issuer`, `credential_id`, `credential_url`, `badge_image`,
                 `description`, `issued_on`, `expires_on`, `is_published`)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
            $this->columns($input)
        );
    }

    /**
     * @param array<string, mixed> $input
     */
    public function update(int $id, array $input): void
    {
        $params   = $this->columns($input);
        $params[] = $id;

        $this->db()->execute(
            'UPDATE certifications SET
                `title` = ?, `issuer` = ?, `credential_id` = ?, `credential_url` = ?,
                `badge_image` = ?, `description` = ?, `issued_on` = ?, `expires_on` = ?,
                `is_published` = ?
              WHERE id = ?',
            $params
        );
    }

    public function delete(int $id): void
    {
        $this->db()->execute('DELETE FROM certifications WHERE id = ?', [$id]);
    }

    /**
     * @param  array<string, mixed> $input
     * @return array<int, mixed>
     */
    private function columns(array $input): array
    {
        return [
            $this->fit((string) ($input['title'] ?? ''), 180),
            $this->fit((string) ($input['issuer'] ?? ''), 120),
            $this->fit((string) ($input['credential_id'] ?? ''), 120),
            $this->fit((string) ($input['credential_url'] ?? ''), 255),
            $this->fit((string) ($input['badge_image'] ?? ''), 255),
            $this->fit((string) ($input['description'] ?? ''), 400),
            $this->nullify((string) ($input['issued_on'] ?? '')),
            // Vacío = no vence. La sección pública ya no muestra estado, pero
            // la fecha sigue siendo el dato.
            $this->nullify((string) ($input['expires_on'] ?? '')),
            (int) ($input['is_published'] ?? 0),
        ];
    }
}
