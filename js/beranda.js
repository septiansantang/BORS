function setupSidebarAjax() {
    const menuLinks = document.querySelectorAll('.tab .list-item a');
    menuLinks.forEach(link => {
        // Hindari double binding
        link.removeEventListener('click', link._borsmenSidebarHandler);
        link._borsmenSidebarHandler = function(e) {
            const href = link.getAttribute('href');
            // Hanya intercept link PHP kecuali logout
            if (href.endsWith('.php') && !href.includes('api.php?action=logout')) {
                e.preventDefault();
                fetch(href)
                    .then(res => res.text())
                    .then(html => {
                        // Ambil #content dari hasil fetch
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const newContent = doc.getElementById('content');
                        if (newContent) {
                            document.getElementById('content').innerHTML = newContent.innerHTML;
                            setupSidebarAjax();
                            if (typeof setupDeleteCampaign === 'function') setupDeleteCampaign();

                            // Tambahkan ini: attach ulang handler untuk form escrow
                            if (href.indexOf('escrow.php') !== -1) {
                                setupEscrowFormHandler();
                            }
                        }
                    })
                    .catch(err => console.error('Error fetching content:', err));
            }
        };
        link.addEventListener('click', link._borsmenSidebarHandler);
    });
}

// Handler submit form escrow agar POST tetap ke escrow.php walau via AJAX
function setupEscrowFormHandler() {
    const form = document.querySelector('#content form[action], #content form');
    if (form && form.querySelector('input[name="jumlah"]')) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(form);
            fetch('escrow.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.text())
            .then(html => {
                // Replace content dengan hasil escrow.php (agar pesan sukses/error muncul)
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newContent = doc.getElementById('content');
                if (newContent) {
                    document.getElementById('content').innerHTML = newContent.innerHTML;
                    setupSidebarAjax();
                    setupEscrowFormHandler();
                }
            })
            .catch(err => alert('Gagal setor dana: ' + err));
        }, { once: true });
    }
}

function setupDeleteCampaign() {
    document.querySelectorAll('.delete-campaign').forEach(button => {
        button.removeEventListener('click', button._borsmenDeleteHandler);
        button._borsmenDeleteHandler = function(e) {
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
        };
        button.addEventListener('click', button._borsmenDeleteHandler);
    });
}

// Jalankan saat halaman pertama kali dimuat
document.addEventListener('DOMContentLoaded', function() {
    setupSidebarAjax();
    setupDeleteCampaign();
    setupEscrowFormHandler();
});
