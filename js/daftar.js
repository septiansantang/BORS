const container = document.querySelector('.container');
const registerBtn = document.querySelector('.register-btn');
const loginBtn = document.querySelector('.login-btn');

registerBtn.addEventListener('click', () => {
    container.classList.add('active');
})

loginBtn.addEventListener('click', () => {
    container.classList.remove('active');
})

document.addEventListener("DOMContentLoaded", () => {
    const hash = window.location.hash; 
    const container = document.querySelector(".container"); 
    const registerForm = document.getElementById("register");

    if (hash === "#register" && container) {
        container.classList.add("active"); 
    }

    const registerBtn = document.querySelector(".register-btn");
    const loginBtn = document.querySelector(".login-btn");

    if (registerBtn) {
        registerBtn.addEventListener("click", () => {
            container.classList.add("active"); 
        });
    }

    if (loginBtn) {
        loginBtn.addEventListener("click", () => {
            container.classList.remove("active"); 
        });
    }
});
const influencerButton = document.querySelector('.btn-influencer');
const businessButton = document.querySelector('.btn-business');

influencerButton.addEventListener('click', function(event) {
    event.preventDefault();
    document.getElementById('register').scrollIntoView();
    document.getElementById('register').classList.add('show-influencer');
});

businessButton.addEventListener('click', function(event) {
    event.preventDefault();
    document.getElementById('register').scrollIntoView();
    document.getElementById('register').classList.add('show-business');
});




const influencerRadio = document.querySelector("input[name='user'][value='influencer']");
const imageContainer = document.querySelector(".image-container");

// Toggle gambar berdasarkan pilihan radio
document.querySelectorAll("input[name='user']").forEach(radio => {
    radio.addEventListener("change", function () {
        if (influencerRadio.checked) {
            imageContainer.classList.add("show");
        } else {
            imageContainer.classList.remove("show");
        }
    });
});





