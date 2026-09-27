<div
x-data="toastManager()"
x-init="init()"
class="fixed top-6 right-6 z-50 space-y-3 w-80">

<template x-for="toast in toasts" :key="toast.id">

<div
x-show="toast.show"
x-transition
:class="{
'bg-[var(--success)]': toast.type === 'success',
'bg-[var(--danger)]': toast.type === 'error',
'bg-[var(--info)]': toast.type === 'info'
}"
class="text-white px-4 py-3 rounded-lg shadow-lg flex justify-between items-start">

<span x-text="toast.message"></span>

<button
@click="remove(toast.id)"
class="ml-3 text-white/70 hover:text-white">

✕

</button>

</div>

</template>

</div>

<script>
function toastManager(){

return {

toasts: [],

init(){

document.addEventListener('toast',event=>{

this.toast(event.detail.message,event.detail.type)

})

@if(session('success'))
this.toast("{{ session('success') }}",'success')
@endif

@if(session('error'))
this.toast("{{ session('error') }}",'error')
@endif

@if ($errors->any())
this.toast("{{ $errors->first() }}",'error')
@endif

},

toast(message,type='info'){

let id = Date.now()

this.toasts.push({
id,
message,
type,
show:true
})

setTimeout(()=>this.remove(id),4000)

},

remove(id){

this.toasts = this.toasts.filter(t=>t.id !== id)

}

}

}
</script>
