document.addEventListener("DOMContentLoaded", function () {
    const kotaSelect = document.getElementById("kota");

    const daftarKota = ["Jakarta", "Bandung", "Surabaya", "Yogyakarta", "Medan", "Makassar", "Palembang", "Semarang", "Bekasi", "Depok", "Tangerang", "Bogor", "Cirebon", "Pontianak", "Banjarmasin", "Balikpapan", "Samarinda", "Manado", "Kupang", "Jayapura", "Ambon", "Merauke", "Sorong", "Ternate", "Gorontalo", "Palu", "Kendari", "Makale", "Parepare", "Watampone", "Bima", "Mataram", "Lombok", "Sumbawa", "Banyuwangi", "Jember", "Kediri", "Madiun", "Ponorogo", "Trenggalek", "Tulungagung", "Blitar", "Malang", "Probolinggo", "Pasuruan", "Situbondo", "Bondowoso", "Jember", "Lumajang", "Pamekasan", "Sampang", "Sumenep", "Bangkalan", "Gresik", "Lamongan", "Tuban", "Bojonegoro", "Ngawi", "Magetan", "Ponorogo", "Pacitan", "Trenggalek", "Klaten", "Sleman", "Bantul", "Kulon Progo", "Gunung Kidul", "Wonogiri", "Sragen", "Karanganyar", "Wonogiri", "Purbalingga", "Banjarnegara", "Pekalongan", "Batang", "Kendal", "Temanggung", "Wonosobo", "Magelang", "Purworejo", "Kebumen", "Cilacap", "Banyumas", "Purbalingga", "Banjarnegara", "Kuningan", "Cirebon", "Majalengka", "Sumedang", "Garut", "Tasikmalaya", "Ciamis", "Pangandaran", "Banjar", "Cimahi", "Bandung", "Bogor", "Sukabumi", "Cianjur", "Bekasi", "Karawang", "Purwakarta", "Subang", "Indramayu", "Majalengka", "Cirebon", "Kuningan", "Cilacap", "Banyumas", "Purbalingga", "Banjarnegara", "Wonosobo", "Magelang", "Purworejo", "Kebumen", "Yogyakarta", "Bantul", "Gunung Kidul", "Kulon Progo", "Sleman", "Wonogiri", "Sragen", "Karanganyar", "Klaten", "Boyolali", "Purwodadi", "Grobogan", "Blora", "Rembang", "Pati", "Kudus", "Jepara", "Demak", "Semarang", "Salatiga", "Kendal", "Pekalongan", "Batang", "Pemalang", "Tegal", "Brebes", "Purbalingga", "Banjarnegara", "Cilacap", "Banyumas", "Purwokerto", "Wonosobo", "Magelang", "Purworejo", "Kebumen", "Yogyakarta", "Surabaya", "Gresik", "Lamongan", "Tuban", "Bojonegoro", "Ngawi", "Magetan", "Ponorogo", "Trenggalek", "Pacitan", "Kediri", "Madiun", "Ponorogo", "Nganjuk", "Jombang", "Mojokerto", "Sidoarjo", "Pasuruan", "Probolinggo", "Situbondo", "Bondowoso", "Banyuwangi", "Jember", "Lumajang", "Pamekasan", "Sampang", "Sumenep", "Bangkalan", "Gresik", "Lamongan"];

    daftarKota.forEach(kota => {
        let option = document.createElement("option");
        option.value = kota;
        option.textContent = kota;
        kotaSelect.appendChild(option);
    });

    document.getElementById("influencerForm").addEventListener("submit", function (event) {
        const nama = document.getElementById("nama").value.trim();
        if (nama === "") {
            alert("Nama tidak boleh kosong!");
            event.preventDefault();
        }
    });
});
