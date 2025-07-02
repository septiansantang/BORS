function setupDeleteCampaign() {
    document.querySelectorAll('.delete-campaign').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Apakah Anda yakin ingin menghapus kampanye ini?')) {
                const campaignId = this.getAttribute('data-id');
                fetch('delete_kampanye.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id_campaign=${campaignId}`
                })
                .then(res => res.json())
                .then(data => {
                    alert(data.message);
                    if (data.status === 200) {
                        window.location.reload();
                    }
                })
                .catch(err => console.error('Error deleting campaign:', err));
            }
        });
    });
}

// Jalankan saat pertama kali halaman dimuat
setupDeleteCampaign();

// Jika ada AJAX sidebar, panggil ulang setupDeleteCampaign setelah konten diganti
if (typeof window.setupSidebarAjax === 'function') {
    const origSidebarAjax = window.setupSidebarAjax;
    window.setupSidebarAjax = function() {
        origSidebarAjax();
        setupDeleteCampaign();
    };
}
