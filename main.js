// add hovered class to selected list item
let list = document.querySelectorAll(".navigation li");

function activeLink() {
  list.forEach((item) => {
    item.classList.remove("hovered");
  });
  this.classList.add("hovered");
}

list.forEach((item) => item.addEventListener("mouseover", activeLink));

// Menu Toggle
let toggle = document.querySelector(".toggle");
let navigation = document.querySelector(".navigation");
let main = document.querySelector(".main");

toggle.onclick = function () {
  navigation.classList.toggle("active");
  main.classList.toggle("active");
};

document.getElementById('sendBtn').addEventListener('click', function() {
  const sender = document.getElementById('sender').value;
  const message = document.getElementById('message').value;

  if (sender && message) {
      // Send the message to the server using AJAX
      const xhr = new XMLHttpRequest();
      xhr.open('POST', 'messages.php', true);
      xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
      xhr.onload = function() {
          if (this.status == 200) {
              document.getElementById('message').value = ''; // Clear the textarea
              loadMessages(); // Refresh the messages list
          }
      };
      xhr.send('sender=' + sender + '&message=' + message);
  } else {
      alert('Both fields are required!');
  }
});

// Load messages when the page loads
window.onload = loadMessages;

function loadMessages() {
  const xhr = new XMLHttpRequest();
  xhr.open('GET', 'messages.php', true);
  xhr.onload = function() {
      if (this.status == 200) {
          document.getElementById('messages').innerHTML = this.responseText;
      }
  };
  xhr.send();
}
