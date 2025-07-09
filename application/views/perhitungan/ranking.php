<div class="main-panel">
    <div class="content">
        <div class="page-inner">
            <div class="page-header">
                <h4 class="page-title"><?= $title ?></h4>
                <ul class="breadcrumbs">
                    <li class="nav-home">
                        <a href="<?= base_url('admin') ?>">
                            <i class="flaticon-home"></i>
                        </a>
                    </li>
                    <li class="separator">
                        <i class="flaticon-right-arrow"></i>
                    </li>
                    <li class="nav-item">
                        <a href="<?= base_url('perhitungan') ?>">Perhitungan</a>
                    </li>
                    <li class="separator">
                        <i class="flaticon-right-arrow"></i>
                    </li>
                    <li class="nav-item">
                        <a href="<?= base_url('perhitungan/ranking') ?>">Ranking</a>
                    </li>
                </ul>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex align-items-center">
                                <h4 class="card-title">Ranking Guru Periode <?= $periode['waktu'] ?></h4>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if ($this->session->flashdata('done')) { ?>
                                <div class="alert alert-success" role="alert" id="close_alert">
                                    <?= $this->session->flashdata('done'); ?>
                                </div>
                            <?php } else if ($this->session->flashdata('belum')) { ?>
                                <div class="alert alert-warning" role="alert" id="close_alert">
                                    <?= $this->session->flashdata('belum'); ?>
                                </div>
                            <?php } ?>
                            <div class="table-responsive">
                                <table id="add-row" class="display table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Ranking</th>
                                            <th>Nama Guru</th>
                                            <th>Nilai Preferensi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($ranked_teachers)) : ?>
                                            <tr>
                                                <td colspan="3" class="text-center">Belum ada data ranking. Silakan lakukan perhitungan terlebih dahulu.</td>
                                            </tr>
                                        <?php else : ?>
                                            <?php foreach ($ranked_teachers as $rt) : ?>
                                                <tr>
                                                    <td><?= $rt['rank'] ?></td>
                                                    <td><?= $rt['nama_guru'] ?></td>
                                                    <td><?= $rt['preferensi_score'] ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    window.setTimeout(function() {
        $("#close_alert").fadeTo(500, 0).slideUp(500, function() {
            $(this).remove();
        });
    }, 3000);
</script>