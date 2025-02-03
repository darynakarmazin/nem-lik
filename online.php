<?php
/*
 * Template Name: Online registration
 * Description: A Page Template with a Page Builder design.
 */
     $mivaan_redux_demo = get_option('redux_demo');
     get_header('3'); 
?>
<!-- Start Bredcrumb Area -->
<section class="breadcumb-area pt-110 pb-110">
    <div class="container">
        <?= do_shortcode('[breadcrums_shortcode_translate_cust_post]'); ?>
    </div>
</section>
<!-- End Bredcrumb Area -->
<section class="blog-page-area title">
    <div class="container">
        <h2><?= do_shortcode('[title_shortcode_translate_blog]'); ?></h2>
    </div>
</section>

<!-- Start Blog Page -->
<section class="blog-page-area pb-50">
    <div class="container services-container online-registration">
        <div class="row">
			<div class="mb-20 blog-single-item services-single-item">
				<div class="faq-box-item-topline">
					<p class="faq-box-item-topline-question">Поліклінічне відділення</p>
				</div>
			</div>
        </div>
    <form id="registrationForm">
        <div class="registrationForm-part">
            <?php 
                $doctors = get_terms( [
                    'taxonomy' => 'likar',
                    'hide_empty' => false,
                    'meta_key' => 'weekend',
                    'meta_value' => '0'
                ] );
            ?>
            <select name="doctors" id="doctors" class="doctors-select">
                <option value="" selected hidden>Список лікарів</option>
                <?php foreach($doctors as $doctor):?>
                    <option value="<?= $doctor->term_id; ?>"><?= $doctor->name; ?></option>
                <?php endforeach;?>
            </select>
        </div>

    <div class="registrationForm-part time-data">
<div>
    <label class="label" for="localdate">Дата:</label>
    <input type="text" id="localdate" name="date" class="datepicker" required/>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1); // Додаємо один день до поточної дати

        flatpickr("#localdate", {
            altInput: true, 
            altFormat: "F j, Y", 
            dateFormat: "Y-m-d", 
            inline: true,
            locale: {
                firstDayOfWeek: 1,
                weekdays: {
                    shorthand: ["Нд", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб"],
                    longhand: ["Неділя", "Понеділок", "Вівторок", "Середа", "Четвер", "П’ятниця", "Субота"]
                },
                months: {
                    shorthand: ["Січ", "Лют", "Бер", "Кві", "Тра", "Чер", "Лип", "Сер", "Вер", "Жов", "Лис", "Гру"],
                    longhand: ["Січень", "Лютий", "Березень", "Квітень", "Травень", "Червень", "Липень", "Серпень", "Вересень", "Жовтень", "Листопад", "Грудень"]
                }
            },
            minDate: tomorrow.toISOString().split('T')[0], // Мінімальна дата — завтрашній день
            disable: [
                function(date) {
                    return (date.getDay() === 0 || date.getDay() === 6);
                }
            ],
            onDayCreate: function(dObj, dStr, fp, dayElem) {
                const day = new Date(dayElem.dateObj).getDay(); 
                if (day === 0 || day === 6) {
                    dayElem.classList.add('flatpickr-weekend'); 
                }
            }
        });



        // observer for check available time
        const target_day = document.querySelector('.flatpickr-days');
        const target_doktor = document.querySelector('span.current');
        const time_table = document.querySelectorAll('[name="timeslot"]');
        
        time_table.forEach((element) => {
            element.addEventListener('click', function(e){
                if(!document.querySelector('#doctors').value && !document.querySelector('#localdate').value){
                    e.preventDefault();
                    alert('Спочатку оберіть лікаря та дату')
                }else if(!document.querySelector('#doctors').value){
                    e.preventDefault();
                    alert('Спочатку оберіть лікаря ')
                }else if(!document.querySelector('#localdate').value){
                    e.preventDefault();
                    alert('Спочатку оберіть дату')
                }
            })
        });
        
        const config = {
            attributes: true,
            childList: true,
            subtree: true,
        };

        const callback = function (mutationsList, observer) {
            const av_day = document.querySelectorAll('.flatpickr-day:not(.flatpickr-disabled)');
            const cur_date = document.querySelector('#localdate').value;
            const cur_doctor = document.querySelector('#doctors').value;
            const time_loader = document.querySelector('#time_loader');
          
            
            setTime(cur_date, cur_doctor, time_loader);
            
        };
        
        const observer = new MutationObserver(callback);
        observer.observe(target_day, config);
        observer.observe(target_doktor, config);
        
        
        function setTime(date, doctor, loader){
            const time = document.querySelectorAll('[name="timeslot"]')
            time_loader.style.display = 'flex';
            time.forEach((el) => {
                el.removeAttribute('disabled')
                el.closest('label').style.textDecoration = 'none';
                el.closest('label').style.color = '#fff';
                let data = new FormData();
                data.append('date', date);
                data.append('time', el.value);
                data.append('doctor', doctor)
                fetch ('/wp-content/themes/mivaan-child/actions/check-time.php', {
                    method: 'POST',
                    body: data
                }).then(response => response.text())
                .then(data => {
                    let res = data;//.substring(100);
                    // console.log(el)
                    if(res === 'false'){
                        el.setAttribute('disabled', "")
                        el.closest('label').style.textDecoration = 'line-through';
                        el.closest('label').style.color = '#d41515';
                    }
                })
            })
            setTimeout(() => {
                time_loader.style.display = 'none';
            }, 2000);
        }
    });
</script>



    <div class="time-div">
        <p class="label" for="timeslot">Час:</p>
        <div class="time-btn-list">
        <?php
            $start = new DateTime('08:00');
            $end = new DateTime('15:40');
            $interval = new DateInterval('PT1H'); 
            $period = new DatePeriod($start, $interval, $end);

        foreach ($period as $time) {
            $value = $time->format('H:i');
                echo "<label class=\"time-btn\">";
                echo "<input type=\"radio\" name=\"timeslot\" value=\"$value\"> $value";
                echo "</label>";
            }
        ?>
        <div id="time_loader" class="overlay" style="display:none;">
            <div class="form-loader"></div>
        </div>
    </div>
        </div>
    </div>

    <div class="registrationForm-part add-data customrs-data">
        <p class="label">Дані пацієнта:</p>
        <input type="text" id="fullname" name="fullname" placeholder="ПІБ *" required>
        <input type="text" id="phone" name="phone" placeholder="Номер телефону *" required>
        <input type="text" id="email" name="email" placeholder="Email *" required>
    </div>

    <div class="registrationForm-part">
    <fieldset class="checkbox-wrapper">
        <label>з направленням
            <input type="radio" class="modern-radio" id="with_referral" name="referral" value="with_referral" checked>
            <span></span>
        </label>
        <label>без направлення
            <input type="radio" class="modern-radio" id="without_referral" name="referral" value="without_referral" checked>
            <span></span>
        </label>
    </fieldset>
    </div>

    <div class="registrationForm-part">
        <button class="button-1" type="submit">Відправити <i class="fa-solid fa-arrow-right"></i></button>                                    
    </div>
    
</form>

    </div>
</section>
<!-- End Blog Page -->

<!-- Модальне вікно -->
<div id="successModal" class="online-registration modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Дякуємо за запис!<br>З вами зв'яжуться, щоб підтвердити час.</h2>
        <p>*Без підтвердження запис вважається недійсним.</p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registrationForm');
    const modal = document.getElementById('successModal');
    const span = document.getElementsByClassName('close')[0];

    form.addEventListener('submit', (event) => {
        event.preventDefault();

        let isValid = true;

        // Очистити попередні помилки
        document.querySelectorAll('.error').forEach(el => el.remove());
        form.querySelectorAll('.input-error').forEach(el => el.classList.remove('input-error'));

        const phoneInput = form.querySelector('#phone');
        const emailInput = form.querySelector('#email');
        const dateInput = form.querySelector('#localdate');
        const timeInput = form.querySelector('input[name="timeslot"]:checked');

        const phone = phoneInput.value;
        if (!/^\d{10,}$/.test(phone)) {
            isValid = false;
            phoneInput.classList.add('input-error');
            phoneInput.insertAdjacentHTML('afterend', '<div class="error">Номер телефону має містити лише цифри і бути не менше 10 цифр.</div>');
        }

        const email = emailInput.value;
        if (!/\S+@\S+\.\S+/.test(email)) {
            isValid = false;
            emailInput.classList.add('input-error');
            emailInput.insertAdjacentHTML('afterend', '<div class="error">Email має містити символ @.</div>');
        }

        const date = dateInput.value;
        if (!date) {
            isValid = false;
            dateInput.classList.add('input-error');
            dateInput.insertAdjacentHTML('afterend', '<div class="error">Будь ласка, виберіть дату.</div>');
        }

        if (!timeInput) {
            isValid = false;
            form.querySelector('.time-div').insertAdjacentHTML('beforeend', '<div class="error">Будь ласка, виберіть час.</div>');
        }

        if (!isValid) {
            return;
        }

        const formData = new FormData(form);
        const doctor = formData.get('doctors');
        if (!doctor) {
            alert('Будь ласка, виберіть лікаря.');
            return;
        }

        const data = {
            doctor: formData.get('doctors'),
            date: formData.get('date'),
            time: formData.get('timeslot'),
            fullname: formData.get('fullname'),
            phone: formData.get('phone'),
            email: formData.get('email'),
            referral: formData.get('referral')
        };

        if(isValid){
            fetch ('/wp-content/themes/mivaan-child/actions/online-req.php', {
                method: 'POST',
                body: formData
            }).then(ok=>{
                console.log(ok)
                
            })
        }

        console.log(data);
        modal.style.display = 'block';
        form.reset();
    });

    span.onclick = function() {
        modal.style.display = 'none';
        location.reload();
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
            location.reload();
        }
    }
});

</script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<?php
    get_footer();
?>
