
document.addEventListener("DOMContentLoaded", function () {
    const menuItems = document.querySelectorAll(".tab .list-item");
    const contentDiv = document.getElementById("content");

    menuItems.forEach(item => {
        item.addEventListener("click", function (e) {
            e.preventDefault();
            menuItems.forEach(i => i.classList.remove("active"));
            this.classList.add("active");

            const target = this.getAttribute("data-target");
            if (!target) return;

            // Konten placeholder untuk setiap tab
            const contents = {
                dashboard: contentDiv.innerHTML, // Pertahankan konten dashboard
                kampanye: `
                    <h4>Kampanye Saya</h4>
                    <p>Belum ada kampanye yang dibuat.</p>
                `,
                analitik: `
                    <h4>Analitik</h4>
                    <p>Data analitik belum tersedia.</p>
                `,
                escrow: `
                    <h4>Escrow</h4>
                    <p>Informasi escrow belum tersedia.</p>
                `,
                pengaturan: `
                    <h4>Pengaturan</h4>
                    <p>Pengaturan akun Anda.</p>
                `
            };

            contentDiv.innerHTML = contents[target] || "<p>Konten tidak tersedia.</p>";
        });
    });
});
