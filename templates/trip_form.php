<?php
/**
 * Форма поездки (создание и редактирование).
 *
 * @var array    $values      значения полей
 * @var string[] $errors      ошибки валидации
 * @var array[]  $cities      список городов
 * @var string   $submitLabel текст кнопки
 */
?>
<?php if ($errors): ?>
    <div class="error"><ul>
        <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
    </ul></div>
<?php endif; ?>

<form method="post" id="tripForm">
    <p>
        <label for="from_city_id">Откуда</label>
        <select name="from_city_id" id="from_city_id" required>
            <option value="">— выберите —</option>
            <?php foreach ($cities as $c): ?>
                <option value="<?= (int)$c['id'] ?>" <?= (int)($values['from_city_id'] ?? 0) === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </p>
    <p>
        <label for="to_city_id">Куда</label>
        <select name="to_city_id" id="to_city_id" required>
            <option value="">— выберите —</option>
            <?php foreach ($cities as $c): ?>
                <option value="<?= (int)$c['id'] ?>" <?= (int)($values['to_city_id'] ?? 0) === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </p>
    <p>
        <label for="trip_date">Дата</label>
        <input type="date" name="trip_date" id="trip_date" required min="<?= date('Y-m-d') ?>" value="<?= e($values['trip_date'] ?? '') ?>">
    </p>
    <p>
        <label for="trip_time">Время отправления</label>
        <input type="time" name="trip_time" id="trip_time" required value="<?= e($values['trip_time'] ?? '') ?>">
    </p>
    <p>
        <label for="seats">Число мест (1–8)</label>
        <input type="number" name="seats" id="seats" required min="1" max="8" value="<?= e($values['seats'] ?? '') ?>">
    </p>
    <p>
        <label for="price">Цена с человека (лей)</label>
        <input type="number" name="price" id="price" required min="0" max="10000" step="0.01" value="<?= e($values['price'] ?? '') ?>">
    </p>
    <p>
        <label>Багаж</label>
        <label style="font-weight:normal"><input type="radio" name="luggage" value="1" <?= ($values['luggage'] ?? '0') === '1' ? 'checked' : '' ?>> Можно</label>
        <label style="font-weight:normal"><input type="radio" name="luggage" value="0" <?= ($values['luggage'] ?? '0') === '0' ? 'checked' : '' ?>> Нельзя</label>
    </p>
    <p>
        <label for="contact">Контакт (телефон или Telegram)</label>
        <input type="text" name="contact" id="contact" required minlength="3" maxlength="100" value="<?= e($values['contact'] ?? '') ?>">
    </p>
    <p>
        <label for="comment">Комментарий</label>
        <textarea name="comment" id="comment" rows="3" maxlength="500"><?= e($values['comment'] ?? '') ?></textarea>
    </p>
    <p><button type="submit"><?= e($submitLabel) ?></button></p>
</form>

<script>
// Проверка на стороне клиента: города должны различаться.
document.getElementById('tripForm').addEventListener('submit', function (ev) {
    var from = this.from_city_id.value, to = this.to_city_id.value;
    if (from && from === to) {
        ev.preventDefault();
        alert('Города отправления и назначения должны отличаться.');
    }
});
</script>
