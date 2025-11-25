function openForm() {
  document.querySelector(".c-form__toggle").classList.add("hidden");
  document.querySelector(".c-form").classList.add("active");
  document.getElementById("step1").classList.add("active");
}

function nextStep(step) {
  document.getElementById("step" + step).classList.remove("active");
  document.getElementById("step" + (step + 1)).classList.add("active");

  document.getElementById("progress").style.width = (step * 50) + "vw";
}

function finishForm() {
  document.getElementById("progress").style.width = "100vw";
  document.querySelector(".c-form").style.opacity = "0";
  document.querySelector(".c-form").style.transform = "translateX(50%) scaleX(0)";
  document.querySelector(".c-welcome").classList.add("active");

  setTimeout(() => {
    document.getElementById("loginForm").submit();
  }, 800);
}
