import './bootstrap';
import.meta.glob([

'../fonts/**',

]);

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

window.toast = (message,type='info') => {

document.dispatchEvent(
new CustomEvent('toast',{
detail:{message,type}
})
)

}
