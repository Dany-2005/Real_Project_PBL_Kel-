<h1>Edit Supplier</h1>

<form action="/suplier/{{ $suplier->id_suplier }}" method="POST">
@csrf
@method('PUT')

<input type="text" name="nama_suplier" value="{{ $suplier->nama_suplier }}"><br><br>
<input type="text" name="no_hp" value="{{ $suplier->no_hp }}"><br><br>
<textarea name="alamat">{{ $suplier->alamat }}</textarea><br><br>

<button type="submit">Update</button>
</form>

<a href="/suplier">Kembali</a>