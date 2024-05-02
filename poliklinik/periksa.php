<?php
include "koneksi.php";

//cek apakah sudah login
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username'])) {
    header("Location: index.php?page=loginuser");
    exit;
}

// Fungsi untuk mencegah input karakter salah
function input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_GET['page']) && $_GET['page'] == 'periksa') {
    $id_dokter = input($_POST["id_dokter"]);
    $id_pasien = input($_POST["id_pasien"]);
    $tgl_periksa = input($_POST["tgl_periksa"]);
    $obat = input($_POST["obat"]);
    $catatan = input($_POST["catatan"]);

    if (isset($_POST['id'])) {
        $id = $_POST['id'];

        // update data
        $sql = "UPDATE periksa SET id_dokter='$id_dokter', id_pasien='$id_pasien', tgl_periksa='$tgl_periksa', catatan='$catatan', obat='$obat' WHERE id='$id'";
        $hasil = mysqli_query($mysqli, $sql);

        if ($hasil) {
            header("Location:index.php?page=periksa");
        } else {
            echo "<div class='alert alert-danger'>Data gagal disimpan.</div>";
        }
    } else {
        // Jika tidak ada 'id', lakukan operasi insert data baru
        $sql = "INSERT INTO periksa (id_dokter, id_pasien, tgl_periksa, catatan, obat) VALUES ('$id_dokter','$id_pasien','$tgl_periksa', '$catatan', '$obat')";
        $hasil = mysqli_query($mysqli, $sql);

        if ($hasil) {
            header("Location:index.php?page=periksa");
        } else {
            echo "<div class='alert alert-danger'>Data gagal disimpan.</div>";
        }
    }
}

// Hapus data 
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "DELETE FROM periksa WHERE id='$id'";
    $hasil = mysqli_query($mysqli, $sql);
    if ($hasil) {
        header("Location:index.php?page=periksa");
    } else {
        echo "<div class='alert alert-danger'>Gagal menghapus data.</div>";
    }
}

// Jika ada parameter 'id' di URL, ambil data periksa yang sesuai
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $ambil = mysqli_query($mysqli, "SELECT * FROM periksa WHERE id='$id'");
    while ($row = mysqli_fetch_array($ambil)) {
        $id_dokter = $row['id_dokter'];
        $id_pasien = $row['id_pasien'];
        $tgl_periksa = $row['tgl_periksa'];
        $obat = $row['obat'];
        $catatan = $row['catatan'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Poliklinik - Periksa</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css" integrity="sha384-zCbKRCUGaJDkqS1kPbPd7TveP5iyJE0EjAuZQTgFLD2ylzuqKfdKlfG/eSrtxUkn" crossorigin="anonymous">
</head>
<body>
    <div class="container mt-5">
        <form action="index.php?page=periksa" method="post" enctype="multipart/form-data">
            <?php if (isset($_GET['id'])): ?>
                <input type="hidden" name="id" value="<?php echo $_GET['id'] ?>">
            <?php endif; ?>
            <div class="form-group">
                <label for="inputPasien">Pasien</label>
                <select class="form-control" name="id_pasien">
                    <?php
                    $selected = '';
                    $pasien = mysqli_query($mysqli, "SELECT * FROM pasien");
                    while ($data = mysqli_fetch_array($pasien)) {
                        if ($data['id'] == $id_pasien) {
                            $selected = 'selected="selected"';
                        } else {
                            $selected = '';
                        }
                    ?>
                        <option value="<?php echo $data['id'] ?>" <?php echo $selected ?>><?php echo $data['nama'] ?></option>
                    <?php
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="inputPasien">Dokter</label>
                <select class="form-control" name="id_dokter">
                    <?php
                    $selected = '';
                    $dokter = mysqli_query($mysqli, "SELECT * FROM dokter");
                    while ($data = mysqli_fetch_array($dokter)) {
                        if ($data['id'] == $id_dokter) {
                            $selected = 'selected="selected"';
                        } else {
                            $selected = '';
                        }
                    ?>
                        <option value="<?php echo $data['id'] ?>" <?php echo $selected ?>><?php echo $data['nama'] ?></option>
                    <?php
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label>Tanggal Periksa</label>
                <input type="datetime-local" name="tgl_periksa" class="form-control" placeholder="Masukkan Tanggal" value="<?php echo isset($tgl_periksa) ? $tgl_periksa : ''; ?>" required />
            </div>
            <div class="form-group">
                <label>Catatan</label>
                <input type="text" name="catatan" class="form-control" placeholder="Masukkan Catatan" value="<?php echo isset($catatan) ? $catatan : ''; ?>" required />
            </div>
            <div class="form-group">
                <label>Obat</label>
                <input type="text" name="obat" class="form-control" placeholder="Masukkan Obat" value="<?php echo isset($obat) ? $obat : ''; ?>" required />
            </div>

            <button type="submit" name="submit" class="btn btn-primary"><?php echo isset($_GET['id']) ? 'Simpan' : 'Submit'; ?></button>
        </form>

        <div class="table-responsive mt-5">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th scope="col">No</th>
                        <th scope="col">Pasien</th>
                        <th scope="col">Dokter</th>
                        <th scope="col">Tanggal Periksa</th>
                        <th scope="col">Catatan</th>
                        <th scope="col">Obat</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = mysqli_query($mysqli, "SELECT pr.*,d.nama as 'nama_dokter', p.nama as 'nama_pasien' FROM periksa pr LEFT JOIN dokter d ON (pr.id_dokter=d.id) LEFT JOIN pasien p ON (pr.id_pasien=p.id) ORDER BY pr.tgl_periksa DESC");
                    $no = 1;
                    while ($data = mysqli_fetch_array($result)) {
                    ?>
                        <tr>
                            <td><?php echo $no++ ?></td>
                            <td><?php echo $data['nama_pasien'] ?></td>
                            <td><?php echo $data['nama_dokter'] ?></td>
                            <td><?php echo $data['tgl_periksa'] ?></td>
                            <td><?php echo $data['catatan'] ?></td>
                            <td><?php echo $data['obat'] ?></td>
                            <td>
                                <a class="btn btn-success rounded-pill px-3" 
                                href="index.php?page=periksa&id=<?php echo $data['id'] ?>">
                                Ubah</a>
                                <a class="btn btn-danger rounded-pill px-3" 
                                href="index.php?page=periksa&id=<?php echo $data['id'] ?>&aksi=hapus">Hapus</a>
                            </td>
                        </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
