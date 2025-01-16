import './bootstrap';
import toastr from 'toastr';
import 'toastr/build/toastr.min.css';

window.addEventListener('publicationCreated', event => {
    
    toastr.success(event.detail[0].message);

    
    
})
window.addEventListener('mobiliteCreated', event => {
    
    toastr.success(event.detail[0].message);

    
    
})