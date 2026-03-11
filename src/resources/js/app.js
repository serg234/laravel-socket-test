import './bootstrap';
import './echo';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

/**
 * Екрануємо HTML-символи, щоб безпечно вставляти текст у DOM.
 * Це захист від ситуацій, коли в повідомленні є <script>, HTML-теги тощо.
 */
function escapeHtml(value) {
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

/**
 * Додає одне повідомлення в чат без перезавантаження сторінки.
 * Сюди може прийти:
 * - або наше власне повідомлення після успішного fetch POST
 * - або повідомлення іншого користувача через Echo / Reverb
 */
function appendMessageToChat(event) {
    const messagesList = document.getElementById('messages-list');
    if (!messagesList) {
        return;
    }

    // Якщо раніше був текст "Повідомлень поки немає.", прибираємо його
    const emptyText = document.getElementById('empty-messages-text');
    if (emptyText) {
        emptyText.remove();
    }

    // Визначаємо: це наше повідомлення чи від іншого користувача
    const isOwnMessage = Number(event.sender_id) === Number(window.chatConfig?.authUserId);

    // Підпис автора повідомлення
    const senderLabel = isOwnMessage ? 'Ви' : (event.sender_name ?? 'User');

    // Створюємо обгортку для нового повідомлення
    const wrapper = document.createElement('div');

    // Додаємо різний фон:
    // синій — якщо наше повідомлення
    // сірий — якщо повідомлення від співрозмовника
    wrapper.className = `border rounded p-3 ${isOwnMessage ? 'bg-blue-50' : 'bg-gray-50'}`;

    // Вставляємо вміст повідомлення
    wrapper.innerHTML = `
        <div class="text-sm text-gray-600 mb-1">
            <strong>${escapeHtml(senderLabel)}</strong>
        </div>

        <div class="text-gray-900">
            ${escapeHtml(event.body ?? '')}
        </div>

        <div class="text-xs text-gray-500 mt-2">
            ${escapeHtml(event.created_at ?? '')}
        </div>
    `;

    // Додаємо повідомлення в кінець списку
    messagesList.appendChild(wrapper);

    // Прокручуємо сторінку/блок до нового повідомлення
    wrapper.scrollIntoView({ behavior: 'smooth', block: 'end' });
}

document.addEventListener('DOMContentLoaded', () => {
    // ID поточного авторизованого користувача
    const authUserId = window.chatConfig?.authUserId;

    // ID користувача, з яким зараз відкритий чат
    const chatUserId = window.chatConfig?.chatUserId;

    /**
     * Підписка на приватний канал поточного користувача.
     * Наприклад, якщо authUserId = 2, то слухаємо канал users.2
     *
     * Коли сервер надсилає подію message.sent,
     * ми отримуємо її тут у callback.
     */
    if (window.Echo && authUserId) {
        window.Echo.private(`users.${authUserId}`)
            .listen('.message.sent', (event) => {
                /**
                 * Тут дуже важливий фільтр:
                 * ми додаємо повідомлення лише тоді,
                 * коли воно прийшло саме від того користувача,
                 * чат з яким зараз відкритий.
                 *
                 * Інакше, якщо нам напише хтось інший,
                 * його повідомлення теж з’явиться в поточному чаті, що неправильно.
                 */
                if (Number(event.sender_id) !== Number(chatUserId)) {
                    return;
                }

                appendMessageToChat(event);
            });
    }

    // Знаходимо форму, textarea і блок для помилки
    const form = document.getElementById('message-form');
    const textarea = document.getElementById('body');
    const bodyError = document.getElementById('body-error');

    // Якщо ми не на сторінці чату — просто виходимо
    if (!form || !textarea) {
        return;
    }

    /**
     * Прапорець, який не дає відправити повідомлення двічі,
     * поки попередній запит ще не завершився.
     */
    let isSending = false;

    /**
     * Вмикає / вимикає стан "відправка триває"
     * і блокує кнопку та textarea на час запиту.
     */
    const setSendingState = (state) => {
        isSending = state;

        const submitButton = form.querySelector('button[type="submit"]');

        if (submitButton) {
            submitButton.disabled = state;
        }

        textarea.disabled = state;
    };

    /**
     * Основна функція відправки повідомлення.
     * Вона:
     * 1. перевіряє, чи не йде вже відправка
     * 2. валідує текст на фронті
     * 3. робить fetch POST
     * 4. у разі успіху додає повідомлення в чат
     * 5. очищає textarea
     */
    const sendMessage = async () => {
        // Захист від повторної відправки
        if (isSending) {
            return;
        }

        // Очищаємо стару помилку
        if (bodyError) {
            bodyError.textContent = '';
        }

        // Беремо дані прямо з форми так, як вони реально підуть у POST
        const formData = new FormData(form);

        // Дістаємо текст повідомлення з FormData і обрізаємо пробіли по краях
        const body = String(formData.get('body') ?? '').trim();

        // Якщо після trim рядок порожній — не відправляємо
        if (!body) {
            if (bodyError) {
                bodyError.textContent = 'Повідомлення не може бути порожнім.';
            }
            return;
        }

        // На всякий випадок записуємо назад уже очищений текст
        formData.set('body', body);

        // Переводимо форму в стан "йде відправка"
        setSendingState(true);

        try {
            // Відправляємо POST-запит без перезавантаження сторінки
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '',
                },
                body: formData,
            });

            const data = await response.json();

            // Якщо сервер повернув помилку валідації або іншу помилку
            if (!response.ok) {
                if (data?.errors?.body?.[0] && bodyError) {
                    bodyError.textContent = data.errors.body[0];
                } else if (bodyError) {
                    bodyError.textContent = 'Не вдалося відправити повідомлення.';
                }

                return;
            }

            /**
             * Якщо сервер успішно зберіг повідомлення,
             * додаємо його в чат одразу на стороні відправника.
             *
             * Це потрібно, бо broadcast у нас іде через toOthers(),
             * тобто власному підключенню це повідомлення не повертається.
             */
            if (data?.message) {
                appendMessageToChat(data.message);
            }

            // Очищаємо поле після успішної відправки
            textarea.value = '';

            // Повертаємо фокус у textarea, щоб можна було друкувати далі
            textarea.focus();
        } catch (error) {
            console.error('Message send failed:', error);

            if (bodyError) {
                bodyError.textContent = 'Не вдалося відправити повідомлення.';
            }
        } finally {
            // У будь-якому випадку розблоковуємо форму
            setSendingState(false);
        }
    };

    /**
     * Перехоплюємо стандартну відправку форми,
     * щоб сторінка не перезавантажувалась.
     */
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        await sendMessage();
    });
});
