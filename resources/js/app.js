import './bootstrap';
import toastr from 'toastr';
import 'toastr/build/toastr.min.css';
// resources/js/app.js

import './../../vendor/power-components/livewire-powergrid/dist/powergrid'
// resources/js/app.js

import './../../vendor/power-components/livewire-powergrid/dist/tailwind.css'
window.addEventListener('publicationCreated', event => {
    
    toastr.success(event.detail[0].message);

    
    
})
window.addEventListener('mobiliteCreated', event => {
    
    toastr.success(event.detail[0].message);

    
    
})