
// buttons
const btnNext = document.getElementById('Next');
const btnNext2 = document.getElementById('Next2');
const btnNext3 = document.getElementById('Next3');
const btnNext4 = document.getElementById('Next4');
const btnBack = document.getElementById('Back');
const btnBack2 = document.getElementById('Back2');

// content
var Header = document.getElementById('header');
var Footer = document.getElementById('footer');
var Form1 = document.getElementById('FormBox1');
var Form2 = document.getElementById('FormBox2');
var Body = document.getElementById('body');
var Background = document.getElementById('background');
var secondBackground = document.getElementById('second-background');
var Step1 = document.getElementById('Step1');
var Step2 = document.getElementById('Step2');
var Step3 = document.getElementById('Step3');
var Loading = document.getElementById('Loading');



// step-1 to step 1-1
btnNext.onclick = function() {
    Form1.classList.remove('active');
    Form2.classList.add('active');
    Body.classList.add('background__active');
    secondBackground.classList.remove('background__deactive');
    secondBackground.classList.add('background__active');
    Background.classList.add('background__deactive');
}




// step 1-1 to step 2
btnNext2.onclick = function() {
    Step1.classList.add('deactive');
    Form2.classList.remove('active');
    Step2.classList.add('active');
    Step2.classList.remove('deactive');
    Body.classList.remove('background__active');
    secondBackground.classList.add('background__deactive');
    secondBackground.classList.remove('background__active');
    Background.classList.remove('background__deactive');
}

// step 2 to step 1-1 
btnBack.onclick = function() {
    Step1.classList.remove('deactive');
    Form2.classList.add('active');
    Step2.classList.remove('active');
    Step2.classList.add('deactive');
    Body.classList.add('background__active');
    secondBackground.classList.remove('background__deactive');
    secondBackground.classList.add('background__active');
    Background.classList.add('background__deactive');
}

// step 2 to step 3
btnNext3.onclick = function() {
    Step2.classList.remove('active');
    Step2.classList.add('deactive');
    Loading.classList.add('active');
    Loading.classList.remove('deactive');
    Body.classList.add('background__active');
    Background.classList.add('background__deactive');
    Footer.classList.add('deactive');
    setTimeout(function(){
        Loading.classList.remove('active');
        Loading.classList.add('deactive');
        Step3.classList.add('active');
        Step3.classList.remove('deactive');
        Body.classList.remove('background__active');
        Background.classList.remove('background__deactive');
        Background.classList.add('background__active');
        Footer.classList.remove('deactive');
    }, 1000)
}

// step 3 to step 2
btnBack2.onclick = function() {
    Step2.classList.remove('deactive');
    Step2.classList.add('active');
    Step3.classList.remove('active');
    Step3.classList.add('deactive');
}


// select option
document.querySelectorAll('.dropdown').forEach(function (dropDownWrapper) {
        
        const dropDownBtn = dropDownWrapper.querySelector('.dropdown__btn');
        const dropDownList = dropDownWrapper.querySelector('.dropdown__list');
        const dropDownListItem = dropDownWrapper.querySelectorAll('.dropdown__list-item');
        const hiddenInput = dropDownWrapper.querySelector('.dropdown__input-hidden');

        // open select option

        dropDownBtn.addEventListener('click', function() {
            dropDownList.classList.toggle('visible');
            dropDownBtn.classList.toggle('dropdown-btn__active');
        })

        // choose list item

        dropDownListItem.forEach(function (listItem) {
            listItem.addEventListener('click', function(e) {
                e.stopPropagation();
                dropDownBtn.innerText = this.innerText;
                dropDownBtn.focus();
                hiddenInput.value = this.dataset.value;
                dropDownList.classList.remove('visible');
                dropDownBtn.classList.remove('dropdown-btn__active');
            })
        })

        dropDownListItem.forEach(item =>{ 
            item.addEventListener('click', function(e) {
            dropDownListItem.forEach(el=>{ el.classList.remove('dropdown-list-item__choosen'); });
            item.classList.add('dropdown-list-item__choosen')
        })
        })
        // close list by click off the list

        document.addEventListener('click', function (e) {
            if (e.target !== dropDownBtn) {
                dropDownList.classList.remove('visible');
                dropDownBtn.classList.remove('dropdown-btn__active');
            }
        })

        // close list by press tab or esc

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Tab' || e.key === 'Escape' ) {
                dropDownList.classList.remove('visible');
                dropDownBtn.classList.remove('dropdown-btn__active');
            }
        })

})


// checkbox

// $(document).ready(function() {

//     $.each($('.step-3__checkbox'), function(index, val) {
//         if($(this).find('.step-3__checkbox-input').prop('checked')==true){
//             $(this).addClass('activeted');
//         }
//     })


// })






