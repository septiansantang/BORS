const menubtn = document.getElementById("menu-bors")
const navLinks = document.getElementById("nav_link")
const menubtnicon = menubtn.querySelector("i")

menubtn.addEventListener("click", (e) => {
 navLinks.classList.toggle("open");

 const isopen = navLinks.classList.contains("open");
 menubtnicon.setAttribute("class", isopen ? "ri-close-line" : "ri-menu-line")
})

navLinks.addEventListener("click", (e) =>{
    navLinks.classList.remove("open");
    menubtnicon.setAttribute("class","ri-menu-line" );
});

const daftarButton = document.querySelector('.btn.sign-in');
const masukButton = document.querySelector('.btn.sign-up');

daftarButton.addEventListener('click', function () {
    window.location.href = "daftar.html#register"; 
});

masukButton.addEventListener('click', function () {
    window.location.href = "daftar.html#login"; 
});

const scrollRevealoption = {
    distance: "50px",
    origin: "bottom",
    duration : 1000,
};

ScrollReveal().reveal(".header_image img",{
    ...scrollRevealoption,
    origin: "right",
});

ScrollReveal().reveal(".header_content h1",{
    ...scrollRevealoption,
    delay: 500,
});

ScrollReveal().reveal(".header_content p",{
    ...scrollRevealoption,
    delay: 1000,
});

ScrollReveal().reveal(".header_content form",{
    ...scrollRevealoption,
    delay: 1500,
});

ScrollReveal().reveal(".header_content .bar",{
    ...scrollRevealoption,
    delay: 2000,
});


ScrollReveal().reveal(".header_image_card",{
    duration: 1000,
    interval : 500,
    delay:2500,

});

