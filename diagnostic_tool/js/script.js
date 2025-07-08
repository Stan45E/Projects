// ✅ Copy-to-clipboard
function copyText(text) {
  navigator.clipboard.writeText(text).then(() => {
    new bootstrap.Toast(document.getElementById("copyToast")).show();
  });
}

// ✅ Search filter
document.getElementById("search").addEventListener("input", function () {
  const query = this.value.toLowerCase();
  document.querySelectorAll(".command-item").forEach(item => {
    item.style.display = item.textContent.toLowerCase().includes(query) ? "block" : "none";
  });
});

// ✅ Sidebar category filter
document.querySelectorAll(".category-link").forEach(link => {
  link.addEventListener("click", function (e) {
    e.preventDefault();
    const selected = this.dataset.category;
    console.log("Clicked category:", selected);

    document.querySelectorAll(".command-item").forEach(cmd => {
      const cat = cmd.dataset.category;
      console.log("CMD category:", cat);
      if (selected === "all" || selected === cat) {
        cmd.classList.remove("d-none");
      } else {
        cmd.classList.add("d-none");
      }
    });

    document.querySelectorAll(".category-link").forEach(l => l.classList.remove("active"));
    this.classList.add("active");
  });
});


// ✅ Dark mode toggle
document.getElementById("darkModeSwitch").addEventListener("change", function () {
  document.body.classList.toggle("dark-mode", this.checked);
  localStorage.setItem("darkMode", this.checked ? "on" : "off");
});
window.addEventListener("load", function () {
  const dark = localStorage.getItem("darkMode") === "on";
  document.getElementById("darkModeSwitch").checked = dark;
  document.body.classList.toggle("dark-mode", dark);
});

// ✅ Syntax checker with live feedback and toast
document.getElementById("syntaxInput").addEventListener("keyup", function (e) {
  const value = this.value.trim().toLowerCase();
  const result = document.getElementById("syntaxResult");
  const toast = new bootstrap.Toast(document.getElementById("syntaxToast"));

  const validCommands = [
    "ipconfig", "ping", "tracert", "netstat", "nslookup", "netsh",
    "arp", "systeminfo", "sfc", "chkdsk", "shutdown", "wmic",
    "hostname", "getmac"
  ];

  if (value === "") {
    result.innerText = "";
  } else if (validCommands.some(cmd => value.startsWith(cmd))) {
    result.innerHTML = "✅ Looks like a valid command.";
    result.classList.remove("text-danger");
    result.classList.add("text-success");
  } else {
    result.innerHTML = "❌ Might be an invalid or incomplete command.";
    result.classList.remove("text-success");
    result.classList.add("text-danger");
    if (e.key === "Enter") toast.show();
  }
});

// ✅ Real CMD Execution (uses run.php)
//function runRealCommand() {
 // const command = document.getElementById("runInput").value.trim();
 // const remoteIp = document.getElementById("remoteIp")?.value.trim() || "";

 // fetch("../actions/run.php", {
 //   method: "POST",
 //   headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
 //   body: `cmd=${encodeURIComponent(command)}&remote_ip=${encodeURIComponent(remoteIp)}`
 // })
 // .then(res => res.text())
 // .then(data => {
 //   document.getElementById("runOutput").innerHTML = data;
//  });
//}

function runRealCommand() {
  const cmd = document.getElementById("runInput").value.trim();
  const output = document.getElementById("runOutput");
  const box = document.getElementById("runResult");

  if (!cmd) return;

  output.innerText = "Running...";
  box.classList.remove("d-none");

  fetch("../actions/run.php", {
    method: "POST",
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `cmd=${encodeURIComponent(cmd)}`
  })
    .then(res => res.text())
    .then(data => output.innerText = data)
    .catch(err => output.innerText = "❌ Failed to execute command.");
}


