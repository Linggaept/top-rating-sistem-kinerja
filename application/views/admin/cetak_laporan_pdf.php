<!DOCTYPE html>
<html>

<head>
    <title>Laporan Penilaian Kinerja Guru</title>
</head>
<style type="text/css">
    body {
        font-family: 'Times New Roman', Times, serif;
        font-size: 12px
    }

    .center-text {
        text-align: center;
    }

    .table-bordered {
        border: 1px solid black;
        border-collapse: collapse;
        width: 100%; /* Ensure table takes full width */
    }

    .table-bordered th,
    .table-bordered td {
        border: 1px solid black;
        padding: 8px;
        text-align: left;
    }
</style>

<body>
    <div class="center-text">
        <h3>LAPORAN HASIL PENILAIAN KINERJA GURU</h3>
        <h3>PERIODE PENILAIAN TAHUN <?= $periode['waktu'] ?></h3>
    </div>
    <br>
    <!-- Ensure the table is within a block-level element -->
    <br>
    <table class="table-bordered">
        <tr>
            <th>Ranking</th>
            <th>Nama Guru</th>
            <th>Nilai Preferensi</th>
        </tr>
        <?php foreach ($ranked_teachers as $rt) : ?>
            <tr>
                <td><?= $rt['rank'] ?></td>
                <td><?= $rt['nama_guru'] ?></td>
                <td><?= $rt['preferensi_score'] ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>