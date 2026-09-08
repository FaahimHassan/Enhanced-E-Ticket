const searchForm=document.getElementById('route-search');
async function searchRoutes(event){
 if(event) event.preventDefault();
 const area=document.getElementById('route-results');
 area.textContent='Searching journeys…';
 try{
  const response=await fetch('api/search_routes.php?'+new URLSearchParams(new FormData(searchForm)));
  const trips=await response.json(); if(!response.ok)throw new Error(trips.error||'Search failed.');
  area.replaceChildren(); document.getElementById('result-count').textContent=trips.length+' journeys found';
  if(!trips.length){area.innerHTML='<div class="card"><h3>No journeys found</h3><p>Try another city or date.</p></div>';return;}
  trips.forEach(trip=>{
   const card=document.createElement('article');card.className='trip-card';
   const top=document.createElement('div');top.className='trip-top';
   const title=document.createElement('h3');title.textContent=trip.provider_name||'E-Ticket Express';
   const tag=document.createElement('span');tag.className='tag';tag.textContent=trip.transport_type.toUpperCase();top.append(title,tag);
   const route=document.createElement('div');route.className='trip-route';
   [trip.departure_time.slice(11,16)+' · '+trip.source,'→',trip.arrival_time.slice(11,16)+' · '+trip.destination].forEach(text=>{const el=document.createElement('strong');el.textContent=text;route.append(el);});
   const bottom=document.createElement('div');bottom.className='trip-bottom';
   const seats=document.createElement('span');seats.textContent=trip.available_seats+' seats available';
   const price=document.createElement('strong');price.textContent='৳ '+Number(trip.price).toLocaleString();
   const link=document.createElement('a');link.className='button';link.href='booking.php?id='+trip.schedule_id;link.textContent=trip.available_seats>0?'Select seat →':'View journey';bottom.append(seats,price,link);card.append(top,route,bottom);area.append(card);
  });
 }catch(error){area.textContent=error.message+' Please try again.';}
}
searchForm.addEventListener('submit',searchRoutes);searchRoutes();
