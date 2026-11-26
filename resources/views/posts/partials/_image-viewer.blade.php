@once
<style>
 .post-image-dialog{border:0;padding:52px 12px 12px;background:#10151b;color:white;width:96vw;max-width:1400px;height:94dvh;max-height:94dvh;border-radius:14px}
 .post-image-dialog::backdrop{background:#000d}.post-image-dialog img{width:100%;height:100%;object-fit:contain}
 .post-image-dialog button{position:absolute;top:10px;inset-inline-end:14px;border:1px solid #ffffff66;border-radius:50%;width:36px;height:36px;background:#202934;color:white;font-size:24px;cursor:pointer}
 .pf-media a{display:block}.pf-media a:focus-visible,.pf-thumb:focus-visible{outline:3px solid var(--primary-500);outline-offset:2px}
</style>
<script>
document.addEventListener('click',event=>{
 const link=event.target.closest('a[data-full-image]');
 if(!link||event.ctrlKey||event.metaKey||event.shiftKey||event.altKey)return;
 event.preventDefault();
 let dialog=document.getElementById('postImageDialog');
 if(!dialog){
  dialog=document.createElement('dialog');dialog.id='postImageDialog';dialog.className='post-image-dialog';
  dialog.setAttribute('aria-label',@json(__('View full image')));
  const close=document.createElement('button');close.type='button';close.textContent='×';close.setAttribute('aria-label',@json(__('Close')));
  const photo=document.createElement('img');photo.alt='';
  dialog.append(close,photo);document.body.append(dialog);
  close.addEventListener('click',()=>dialog.close());
  dialog.addEventListener('click',e=>{if(e.target===dialog)dialog.close()});
  dialog.addEventListener('close',()=>{document.body.style.overflow=dialog.dataset.previousOverflow||'';dialog._trigger?.focus()});
 }
 dialog._trigger=link;dialog.querySelector('img').src=link.href;
 dialog.dataset.previousOverflow=document.body.style.overflow;document.body.style.overflow='hidden';dialog.showModal();
});
</script>
@endonce
