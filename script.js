function showTab(tab) {
  document.getElementById('list-tab').classList.add('hidden');
  document.getElementById('register-tab').classList.add('hidden');
  document.getElementById(tab + '-tab').classList.remove('hidden');
}

document.getElementById('register-form').addEventListener('submit', function(e) {
  e.preventDefault();
  const form = this;
  fetch(window.location.href, {
    method: 'POST',
    headers: {'X-Requested-With': 'XMLHttpRequest'},
    body: new FormData(form)
  })
  .then(response => response.json())
  .then(data => {
    if (data.popup) {
      alert(data.popup);
      showTab('register');
    } else if (data.success) {
      alert(data.message || 'Success!');
      form.reset();
      document.getElementById('user_id').value = '';
      document.getElementById('form_action').value = 'register';
      document.querySelector('[name="password"]').required = true;
      showTab('list');
      location.reload();
    } else {
      alert(data.message || 'Operation failed.');
    }
  })
  .catch(err => { form.submit(); });
});

function deleteUser(id) {
  if (!confirm('Are you sure you want to delete this user?')) return;
  fetch(window.location.href, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
      'X-Requested-With': 'XMLHttpRequest'
    },
    body: `action=delete&id=${id}`
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) location.reload();
    else alert(data.message || 'Delete failed');
  })
  .catch(() => location.reload());
}

function editUser(user) {
  showTab('register');
  document.getElementById('user_id').value = user.id;
  document.getElementById('form_action').value = 'update';
  document.querySelector('[name="fullname"]').value = user["Full Name"];
  document.querySelector('[name="username"]').value = user["User name"];
  document.querySelector('[name="dob"]').value = user["Date of Birth"];
  document.querySelector('[name="email"]').value = user["Email"];
  document.querySelector('[name="password"]').required = false;
  document.getElementById('password-field').style.display = 'none';
}
