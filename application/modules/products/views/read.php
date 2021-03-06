<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="row">
        <h1 class="h3 mb-4 text-gray-800 ml-3"><?= $items['0']['nama']; var_dump($detail) ?></h1>
        <a class="btn btn-primary h3 mb-4 ml-auto mr-3" href="<?= base_url('products'); ?>">Daftar Item</a> 
        
    </div>
    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Foto</th>
                            <th>Nama</th>
                            <th>Biaya</th>
                            <th>Deposit</th>
                            <th>Kategori</th>
                            <th>Merk</th>
                            <th>Kondisi</th>
                            <th>Deskripsi</th>
                            <th>Antar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        foreach ($items as $p) : 
                            switch ($p['antar']) {
                                case '1':
                                    $antar = "Ya";
                                    break;
                                
                                default:
                                    $antar = "Tidak";
                                    break;
                            }
                            
                            switch ($p['kondisi']) {
                                case '1':
                                    $kondisi = "Baru";
                                    break;                        
                                case '2':
                                    $kondisi = "Sangat Bagus";
                                    break;                            
                                case '3':
                                    $kondisi = "Bagus";
                                    break;                        
                                case '5':
                                    $kondisi = "Layak";
                                    break;                                                    
                                default:
                                    $kondisi = "Rapuh";
                                    break;
                            }
                            
                            ?>
                            <tr>
                                <td>
                                    <center>
                                        <img src="<?= base_url('assets/img/produk/'), $p['foto'] ?>" height="50px">
                                    </center>
                                </td>
                                <td><?= $p['nama'] ?></td>
                                <td>Rp. <?= number_format($p['harga'],0,",",".");?></td>
                                <td>Rp. <?= number_format($p['deposit'],0,",",".");?></td>
                                <td><?= $p['sub_kat'] ?></td>
                                <td><?= $p['merk'] ?></td>
                                <td><?= $kondisi ?></td>
                                <td><?= $p['deskripsi'] ?></td>
                                <td><?= $antar ?></td>
                            </tr>
                        <? endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>