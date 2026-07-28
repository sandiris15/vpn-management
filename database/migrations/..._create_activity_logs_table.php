public function up()
{
    Schema::create('activity_logs', function (Blueprint $table) {
        $table->id();
        $table->string('username'); // Siapa yang melakukan aksi (admin/operator)
        $table->string('action');    // Jenis aksi (Tambah / Hapus)
        $table->text('description'); // Keterangan detail aksi
        $table->timestamps();
    });
}
