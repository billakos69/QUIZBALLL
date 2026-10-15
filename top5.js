const top5Input = document.getElementById('top5Input');
const top5AutocompleteList = document.getElementById('top5AutocompleteList');

top5Input.addEventListener('input', async function(){
    const q = this.value.trim();
    if(q.length < 1){
        top5AutocompleteList.style.display='none';
        top5AutocompleteList.innerHTML='';
        return;
    }

    const res = await fetch('autocomplete.php?q='+encodeURIComponent(q));
    const items = await res.json();
    top5AutocompleteList.innerHTML='';
    if(!items || items.length === 0){
        top5AutocompleteList.style.display='none';
        return;
    }

    items.forEach(item=>{
        const div = document.createElement('div');
        div.className = 'autocomplete-item';

        if(!item.image){
            div.classList.add('no-img');
        }

        let imgHtml = '';
        if(item.image) {
            imgHtml = `<img src="${item.image}" alt="">`;
        }

        div.innerHTML = `
            ${imgHtml}
            <div class="meta">
                <div class="name">${item.name}</div>
                <div class="team">${item.team || ''}</div>
            </div>
        `;
        div.onclick = ()=>{
            top5Input.value = item.name;
            top5AutocompleteList.style.display='none';
        };
        top5AutocompleteList.appendChild(div);
        requestAnimationFrame(()=>div.classList.add('visible'));
    });
    top5AutocompleteList.style.display='block';
});

document.addEventListener('click', e=>{
    if(!top5AutocompleteList.contains(e.target) && e.target!==top5Input){
        top5AutocompleteList.style.display='none';
    }
});
