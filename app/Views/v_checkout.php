<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<div class="row">
    <div class="col-lg-6">
        <?= form_open('buy', 'class="row g-3"') ?>
        <?= form_hidden('username', session()->get('username')) ?>
        <?= form_input(['type' => 'hidden', 'name' => 'total_harga_produk', 'id' => 'total_harga_produk', 'value' => $total]) ?>
        <?= form_input(['type' => 'hidden', 'name' => 'total_harga_final', 'id' => 'total_harga_final', 'value' => '']) ?>
        <?= form_input(['type' => 'hidden', 'name' => 'ppn_value', 'id' => 'ppn_value', 'value' => '']) ?>
        <?= form_input(['type' => 'hidden', 'name' => 'biaya_admin_value', 'id' => 'biaya_admin_value', 'value' => '']) ?>

        <div class="col-12">
            <label for="nama" class="form-label">Nama</label>
            <input type="text" class="form-control" id="nama" value="<?php echo session()->get('username'); ?>" readonly>
        </div>
        <div class="col-12">
            <label for="alamat" class="form-label">Alamat</label>
            <input type="text" class="form-control" id="alamat" name="alamat" required>
        </div> 
        <div class="col-12">
            <label for="kelurahan" class="form-label">Kelurahan</label>
            <select class="form-control" id="kelurahan" name="kelurahan" required></select>
        </div>
        <div class="col-12">
            <label for="layanan" class="form-label">Layanan</label>
            <select class="form-control" id="layanan" name="layanan" required></select>
        </div>
        <div class="col-12">
            <label for="ongkir_display" class="form-label">Ongkir</label>
            <input type="text" class="form-control" id="ongkir_display" name="ongkir_display" readonly>
            <?= form_input(['type' => 'hidden', 'name' => 'ongkir', 'id' => 'ongkir_hidden', 'value' => '']) ?>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="col-12">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">Nama</th>
                        <th scope="col">Harga</th>
                        <th scope="col">Jumlah</th>
                        <th scope="col">Sub Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 1;
                    if (!empty($items)) :
                        foreach ($items as $index => $item) :
                    ?>
                            <tr>
                                <td><?php echo $item['name'] ?></td>
                                <td><?php echo number_to_currency($item['price'], 'IDR') ?></td>
                                <td><?php echo $item['qty'] ?></td>
                                <td><?php echo number_to_currency($item['price'] * $item['qty'], 'IDR') ?></td>
                            </tr>
                    <?php
                        endforeach;
                    endif;
                    ?>
                    <tr>
                        <td colspan="2"></td>
                        <td>Subtotal Produk</td>
                        <td><?php echo number_to_currency($total, 'IDR') ?></td>
                    </tr>
                    <tr>
                        <td colspan="2"></td>
                        <td>Biaya Admin</td>
                        <td id="biaya_admin_display">IDR 0</td>
                    </tr>
                    <tr>
                        <td colspan="2"></td>
                        <td>PPN (11%)</td>
                        <td id="ppn_display">IDR 0</td>
                    </tr>
                    <tr>
                        <td colspan="2"></td>
                        <td>Ongkir</td>
                        <td id="ongkir_table_display">IDR 0</td>
                    </tr>
                    <tr>
                        <td colspan="2"></td>
                        <td>Grand Total</td>
                        <td><span id="grand_total_display">IDR 0</span></td>
                    </tr>
                </tbody>
            </table>
            </div>
        <div class="text-center">
            <button type="submit" class="btn btn-primary">Buat Pesanan</button>
        </div>
        </form></div>
</div>
<?= $this->endSection() ?>

<?= $this->section('script') ?>
<script>
$(document).ready(function() {
    var ongkir = 0;
    var totalProduk = parseFloat($('#total_harga_produk').val()); 
    var biayaAdmin = 0;
    var ppn = 0;
    var grandTotal = 0;

   
    function formatRupiah(angka) {
        var reverse = angka.toString().split('').reverse().join(''),
            ribuan = reverse.match(/\d{1,3}/g);
        ribuan = ribuan.join('.').split('').reverse().join('');
        return 'IDR ' + ribuan;
    }

    function hitungBiayaAdmin(totalHargaProduk) {
        if (totalHargaProduk <= 20000000) {
            return totalHargaProduk * 0.006; 
        } else if (totalHargaProduk <= 40000000) { 
            return totalHargaProduk * 0.008;
        } else { 
            return totalHargaProduk * 0.001;
        }
    }

    function hitungPPN(totalHargaProduk) {
        return totalHargaProduk * 0.11;
    }

    function hitungSemuaTotal() {
        biayaAdmin = hitungBiayaAdmin(totalProduk);
        ppn = hitungPPN(totalProduk);
        grandTotal = totalProduk + ongkir + biayaAdmin + ppn;

       
        $('#total_harga_final').val(grandTotal);
        $('#ppn_value').val(ppn);
        $('#biaya_admin_value').val(biayaAdmin);
        $('#ongkir_hidden').val(ongkir);

       
        $('#ongkir_display').val(formatRupiah(ongkir));
        $('#ongkir_table_display').html(formatRupiah(ongkir));
        $('#biaya_admin_display').html(formatRupiah(biayaAdmin));
        $('#ppn_display').html(formatRupiah(ppn));
        $('#grand_total_display').html(formatRupiah(grandTotal));
    }

    
    hitungSemuaTotal();

    $('#kelurahan').select2({
        placeholder: 'Ketik nama kelurahan...',
        ajax: {
            url: '<?= base_url('get-location') ?>',
            dataType: 'json',
            delay: 1500,
            data: function (params) {
                return {
                    search: params.term
                };
            },
            processResults: function (data) {
                return {
                    results: data.map(function(item) {
                        return {
                            id: item.id,
                            text: item.subdistrict_name + ", " + item.district_name + ", " + item.city_name + ", " + item.province_name + ", " + item.zip_code
                        };
                    })
                };
            },
            cache: true
        },
        minimumInputLength: 3
    });

    $("#kelurahan").on('change', function() {
        var id_kelurahan = $(this).val(); 
        $("#layanan").empty();
        ongkir = 0; 

        $.ajax({
            url: "<?= site_url('get-cost') ?>",
            type: 'GET',
            data: { 
                'destination': id_kelurahan, 
            },
            dataType: 'json',
            success: function(data) { 
                data.forEach(function(item) {
                    var text = item["description"] + " (" + item["service"] + ") : estimasi " + item["etd"] + "";
                    $("#layanan").append($('<option>', {
                        value: item["cost"],
                        text: text 
                    }));
                });
                if (data.length > 0) {
                    ongkir = parseInt(data[0]["cost"]);
                } else {
                    ongkir = 0; 
                }
                hitungSemuaTotal(); 
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("Error fetching shipping cost: " + textStatus, errorThrown);
                ongkir = 0; // Set ongkir ke 0 jika ada error
                hitungSemuaTotal();
            }
        });
    });

    $("#layanan").on('change', function() {
        ongkir = parseInt($(this).val());
        hitungSemuaTotal();
    }); 
});
</script>
<?= $this->endSection() ?>
