<h1>Tambah Supplier</h1>

<form action="/suplier" method="POST">
@csrf

<input type="text" name="nama_suplier" placeholder="Nama"><br><br>
<input type="text" name="no_hp" placeholder="No HP"><br><br>
<textarea name="alamat" placeholder="Alamat"></textarea><br><br>

<button type="submit">Simpan</button>
</form>

<a href="/suplier">Kembali</a>