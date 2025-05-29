const leftBtn = document.querySelector('.table__button--left');
const rightBtn = document.querySelector('.table__button--right');
const cardsContainer = document.querySelector('.table__cards');

leftBtn.addEventListener('click', () => {
    cardsContainer.scrollBy({ left: -400, behavior: 'smooth' });
});

rightBtn.addEventListener('click', () => {
    cardsContainer.scrollBy({ left: 400, behavior: 'smooth' });
});

document.addEventListener('DOMContentLoaded', function () {
    const editButton = document.getElementById('editModeButton');
    const deleteButton = document.getElementById('deleteModeButton');
    const filterButton = document.getElementById('filterButton');
    const filterDropdown = document.getElementById('filterDropdown');
    const cards = document.querySelectorAll('.table__card');
    let editMode = false;
    let deleteMode = false;
    let filterActive = false;

    console.log('Знайдено карток:', cards.length);
    if (cards.length === 0) {
        console.warn('Картки не знайдені. Перевірте, чи є події на сторінці та чи коректно застосовується клас .table__card');
    }

    editButton.addEventListener('click', function () {
        editMode = !editMode;
        deleteMode = false;
        filterActive = false;
        editButton.classList.toggle('active');
        deleteButton.classList.remove('active');
        filterButton.classList.remove('active');
        filterDropdown.style.display = 'none';

        cards.forEach(card => {
            card.classList.remove('editable', 'deletable', 'hidden');
            if (editMode) {
                card.classList.add('editable');
            }
            card.style.cursor = editMode ? 'pointer' : 'default';
        });
    });

    deleteButton.addEventListener('click', function () {
        deleteMode = !deleteMode;
        editMode = false;
        filterActive = false;
        deleteButton.classList.toggle('active');
        editButton.classList.remove('active');
        filterButton.classList.remove('active');
        filterDropdown.style.display = 'none';

        cards.forEach(card => {
            card.classList.remove('editable', 'deletable', 'hidden');
            if (deleteMode) {
                card.classList.add('deletable');
            }
            card.style.cursor = deleteMode ? 'pointer' : 'default';
        });
    });

    filterButton.addEventListener('click', function () {
        filterActive = !filterActive;
        editMode = false;
        deleteMode = false;
        filterButton.classList.toggle('active');
        editButton.classList.remove('active');
        deleteButton.classList.remove('active');

        filterDropdown.style.display = filterActive ? 'block' : 'none';
        cards.forEach(card => {
            card.classList.remove('editable', 'deletable', 'hidden');
            card.style.cursor = 'default';
        });
    });

    document.querySelectorAll('.filter__item').forEach(item => {
        item.addEventListener('click', function () {
            const filter = this.getAttribute('data-filter');
            console.log('Вибрано фільтр:', filter);
            filterDropdown.style.display = 'none';
            filterButton.classList.add('active');

            cards.forEach(card => {
                const type = card.getAttribute('data-type');
                const priority = card.getAttribute('data-priority');
                const eventDateRaw = card.getAttribute('data-date');

                if (!type || !priority || !eventDateRaw) {
                    console.warn('Відсутні атрибути на картці:', card, { type, priority, eventDateRaw });
                    return;
                }

                const eventDate = new Date(eventDateRaw);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                eventDate.setHours(0, 0, 0, 0);

                if (isNaN(eventDate.getTime())) {
                    console.warn('Невалідна дата на картці:', eventDateRaw, card);
                    return;
                }

                card.classList.remove('hidden');
                if (filter === 'all') {
                    // Показуємо всі картки
                } else if (filter === 'event' || filter === 'task') {
                    if (type !== filter) card.classList.add('hidden');
                } else if (filter === 'high' || filter === 'medium' || filter === 'low') {
                    if (priority !== filter) card.classList.add('hidden');
                } else if (filter === 'future') {
                    if (eventDate < today) card.classList.add('hidden');
                }
            });
        });
    });

    cards.forEach(card => {
        card.addEventListener('click', function () {
            const eventId = card.getAttribute('data-event-id');
            if (editMode) {
                window.location.href = `edit_event.php?id=${eventId}`;
            } else if (deleteMode) {
                if (confirm('Ви впевнені, що хочете видалити цю подію?')) {
                    window.location.href = `delete_event.php?id=${eventId}`;
                }
            }
        });
    });

    const notifButtons = document.querySelectorAll('.card__notif');
    notifButtons.forEach(button => {
        button.addEventListener('click', (event) => {
            event.stopPropagation();
            const eventId = button.closest('.card').dataset.eventId;
            const isActive = button.classList.contains('card__notif--active');

            button.classList.toggle('card__notif--active', !isActive);

            fetch(`schedule_notification.php?event_id=${eventId}&activate=${!isActive}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Помилка сервера: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    console.log('Сповіщення заплановано:', data.message);
                } else {
                    console.error('Помилка:', data.message);
                    button.classList.toggle('card__notif--active', isActive); // Відкат, якщо помилка
                }
            })
            .catch(error => {
                console.error('Помилка запиту:', error);
                button.classList.toggle('card__notif--active', isActive); // Відкат при помилці
            });
        });
    });
});