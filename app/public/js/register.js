document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role');
    const goerFields = document.getElementById('goer-fields');
    const employeeFields = document.getElementById('employee-fields');
    const adminFields = document.getElementById('admin-fields');

    function toggleFields() {
        const selectedRole = roleSelect.value;

        // Скрываем все поля и отключаем их
        goerFields.classList.add('hidden');
        employeeFields.classList.add('hidden');
        adminFields.classList.add('hidden');
        disableFields(goerFields);
        disableFields(employeeFields);
        disableFields(adminFields);

        // Показываем и включаем поля в зависимости от выбранной роли
        if (selectedRole === 'goer') {
            goerFields.classList.remove('hidden');
            enableFields(goerFields);
        } else if (selectedRole === 'employee') {
            employeeFields.classList.remove('hidden');
            enableFields(employeeFields);
        } else if (selectedRole === 'admin') {
            adminFields.classList.remove('hidden');
            enableFields(adminFields);
        }
    }

    function disableFields(container) {
        const inputs = container.querySelectorAll('input, select');
        inputs.forEach(input => input.setAttribute('disabled', 'disabled'));
    }

    function enableFields(container) {
        const inputs = container.querySelectorAll('input, select');
        inputs.forEach(input => input.removeAttribute('disabled'));
    }

    // Инициализация при загрузке страницы
    toggleFields();

    // Обработчик изменения роли
    roleSelect.addEventListener('change', toggleFields);
});