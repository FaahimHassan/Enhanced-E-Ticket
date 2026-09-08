// Browser validation improves feedback; PHP repeats every important check.
document.querySelectorAll('form[data-validate]').forEach(form=>{
 form.addEventListener('submit',event=>{
  let message='';
  if(form.elements.confirm_password && form.elements.password.value!==form.elements.confirm_password.value) message='Passwords do not match.';
  if(form.elements.check_in && form.elements.check_out && form.elements.check_out.value<=form.elements.check_in.value) message='Check-out must be after check-in.';
  if(form.elements.source && form.elements.destination && form.elements.source.value.trim().toLowerCase()===form.elements.destination.value.trim().toLowerCase()) message='Choose different departure and destination cities.';
  if(message){event.preventDefault();const box=form.querySelector('.form-error');if(box)box.textContent=message;else alert(message);}
 });
});
document.querySelectorAll('[data-confirm]').forEach(form=>form.addEventListener('submit',event=>{if(!confirm(form.dataset.confirm))event.preventDefault();}));
