document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role');
    const goerFields = document.getElementById('goer-fields');
    const employeeFields = document.getElementById('employee-fields');
    const adminFields = document.getElementById('admin-fields');

    // Список полей для каждой роли
    const goerInputs = goerFields.querySelectorAll('input');
    const employeeInputs = employeeFields.querySelectorAll('input');
    const adminInputs = adminFields.querySelectorAll('input');

    function toggleFields() {
        const selectedRole = roleSelect.value;

        // Скрываем все поля, отключаем их и убираем required
        goerFields.classList.add('hidden');
        employeeFields.classList.add('hidden');
        adminFields.classList.add('hidden');
        goerInputs.forEach(input => {
            input.disabled = true;
            input.removeAttribute('required');
        });
        employeeInputs.forEach(input => {
            input.disabled = true;
            input.removeAttribute('required');
        });
        adminInputs.forEach(input => {
            input.disabled = true;
            input.removeAttribute('required');
        });

        // Показываем и включаем поля для выбранной роли
        if (selectedRole === 'goer') {
            goerFields.classList.remove('hidden');
            goerInputs.forEach(input => {
                input.disabled = false;
                input.setAttribute('required', 'required');
            });
        } else if (selectedRole === 'employee') {
            employeeFields.classList.remove('hidden');
            employeeInputs.forEach(input => {
                input.disabled = false;
                input.setAttribute('required', 'required');
            });
        } else if (selectedRole === 'admin') {
            adminFields.classList.remove('hidden');
            adminInputs.forEach(input => {
                input.disabled = false;
                input.setAttribute('required', 'required');
            });
        }
    }

    // Инициализация при загрузке страницы
    toggleFields();

    // Обработчик изменения роли
    roleSelect.addEventListener('change', toggleFields);
});