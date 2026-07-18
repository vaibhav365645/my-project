document.addEventListener("DOMContentLoaded", function () {
  const riskData = document.getElementById("riskData");
  const circle = document.getElementById("lodermal");
  const percent = parseFloat(riskData.getAttribute("data-percent")) || 0;

  if (circle) {
    const radius = 85;
    const circumference = 2 * Math.PI * radius; // ~534

    // Ensure dasharray is set
    circle.style.strokeDasharray = `${circumference} ${circumference}`;

    // If percent is 0, offset = circumference (line is hidden)
    // If percent is 100, offset = 0 (line is full)
    const offset = circumference - (percent / 100) * circumference;
    circle.style.strokeDashoffset = offset;
  }
});
