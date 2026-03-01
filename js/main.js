let currentLang="hi";

function toggleLang(){
currentLang=currentLang==="hi"?"en":"hi";
document.querySelectorAll("[data-lang]").forEach(el=>{
el.hidden=el.dataset.lang!==currentLang;
});
}

function toggleMenu(){
const menu=document.getElementById("menu");
menu.style.display=menu.style.display==="flex"?"none":"flex";
}