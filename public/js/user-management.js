(function () {
  const searchInput = document.getElementById('usersSearch');
  const roleFilter = document.getElementById('usersRoleFilter');
  const statusFilter = document.getElementById('usersStatusFilter');
  const resetButtons = [
    document.getElementById('usersResetFilters'),
    document.getElementById('usersClearFilters'),
  ].filter(Boolean);
  const userRows = Array.from(document.querySelectorAll('#all-users-table [data-user-row]'));
  const noResults = document.getElementById('usersNoResults');
  const visibleCount = document.getElementById('usersVisibleCount');

  if (!searchInput || !roleFilter || !statusFilter || !visibleCount) {
    return;
  }

  function normalize(value) {
    return value
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .toLocaleLowerCase()
      .trim();
  }

  function filterUsers() {
    const query = normalize(searchInput.value);
    const selectedRole = normalize(roleFilter.value);
    const selectedStatus = normalize(statusFilter.value);
    let visibleUsers = 0;

    userRows.forEach((row) => {
      const matchesSearch = normalize(row.textContent).includes(query);
      const matchesRole = selectedRole === 'all' || normalize(row.dataset.userRole) === selectedRole;
      const matchesStatus = selectedStatus === 'all' || normalize(row.dataset.userStatus) === selectedStatus;
      const isVisible = matchesSearch && matchesRole && matchesStatus;

      row.hidden = !isVisible;
      if (isVisible) {
        visibleUsers++;
      }
    });

    visibleCount.textContent = String(visibleUsers);
    if (noResults) {
      noResults.hidden = userRows.length === 0 || visibleUsers > 0;
    }
  }

  function resetFilters() {
    searchInput.value = '';
    roleFilter.value = 'all';
    statusFilter.value = 'all';
    filterUsers();
    searchInput.focus();
  }

  searchInput.addEventListener('input', filterUsers);
  roleFilter.addEventListener('change', filterUsers);
  statusFilter.addEventListener('change', filterUsers);
  resetButtons.forEach((button) => button.addEventListener('click', resetFilters));
})();
