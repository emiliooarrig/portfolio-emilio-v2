<?php
/**
 * Perfil — la fila única que alimenta el hero, «Sobre mí» y el pie.
 *
 * Se lee como campo/valor y no como una fila horizontal: son veinte columnas
 * de una sola fila, que en una tabla normal saldría kilométrica.
 *
 * Los datos no llegan por la acción: la fila de perfil ya viene en los datos
 * compartidos de todas las vistas del sitio.
 *
 * @var array<string, mixed> $profile
 */

$fields = [
    ['Nombre',            $profile['full_name'],          'text'],
    ['Rol',               $profile['role_title'],         'text'],
    ['Titular',           $profile['headline'],           'long'],
    ['Bio corta',         $profile['bio_short'],          'long'],
    ['Bio larga',         $profile['bio_long'],           'long'],
    ['Correo',            $profile['email'],              'mail'],
    ['Teléfono',          $profile['phone'],              'text'],
    ['Ubicación',         $profile['location'],           'text'],
    ['Años de experiencia', $profile['years_experience'], 'text'],
    ['Disponible',        $profile['available_for_work'], 'flag'],
    ['Foto',              $profile['avatar_path'],        'file'],
    ['CV',                $profile['cv_path'],            'file'],
    ['GitHub',            $profile['github_url'],         'link'],
    ['LinkedIn',          $profile['linkedin_url'],       'link'],
    ['Sitio web',         $profile['website_url'],        'link'],
];
?>
<?= partial('admin-head', [
    'title' => 'Perfil',
    'lead'  => 'La fila única que alimenta el hero, «Sobre mí» y los datos de contacto.',
    'meta'   => ! empty($profile['updated_at'])
        ? 'Actualizado ' . date('d/m/Y', (int) strtotime((string) $profile['updated_at']))
        : '',
    'action' => ['label' => 'Editar perfil', 'href' => '/admin/perfil/editar'],
]) ?>

<div class="admin-table__wrap">
    <table class="admin-table admin-table--pairs">
        <thead>
            <tr>
                <th scope="col">Campo</th>
                <th scope="col">Valor</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($fields as [$label, $value, $type]): ?>
                <tr>
                    <th scope="row"><?= e($label) ?></th>
                    <td class="admin-table__wide">
                        <?php if ($type === 'flag'): ?>
                            <?= partial('admin-flag', [
                                'on'  => (int) $value === 1,
                                'yes' => 'Sí, abierto a proyectos',
                                'no'  => 'No por ahora',
                            ]) ?>

                        <?php elseif ((string) $value === ''): ?>
                            <span class="admin-table__none">sin definir</span>

                        <?php elseif ($type === 'link'): ?>
                            <a href="<?= e((string) $value) ?>" target="_blank" rel="noopener noreferrer"><?= e((string) $value) ?></a>

                        <?php elseif ($type === 'mail'): ?>
                            <a href="mailto:<?= e((string) $value) ?>"><?= e((string) $value) ?></a>

                        <?php elseif ($type === 'file'): ?>
                            <?php // Ruta relativa a /public: si el archivo no está, el enlace da 404 y eso también es información. ?>
                            <span class="meta"><?= e((string) $value) ?></span>

                        <?php elseif ($type === 'long'): ?>
                            <?php // La bio viene con saltos de línea: se respetan, o se lee como un bloque. ?>
                            <?= paragraphs((string) $value, 'admin-table__p') ?>

                        <?php else: ?>
                            <?= e((string) $value) ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
