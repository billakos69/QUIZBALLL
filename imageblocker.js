document.addEventListener('DOMContentLoaded', function() {
    // όλο το imageblocker.js περιεχόμενο εδώ
// imageblocker.js
let currentCatId = null; // θα ενημερώνεται όταν πατάμε ερώτηση

// 1️⃣ Παρακολουθούμε click στις quiz-item για να ξέρουμε ποιο category είναι ενεργό
document.querySelectorAll('.quiz-item').forEach(item => {
    item.addEventListener('click', () => {
        currentCatId = parseInt(item.dataset.catId);
    });
});

// 2️⃣ MutationObserver για το autocomplete
const autocompleteList = document.getElementById('autocompleteList');
if(autocompleteList){
    const observer = new MutationObserver(() => {
        if(currentCatId !== 3) return; // δουλεύουμε μόνο για category 3

        document.querySelectorAll('#autocompleteList .autocomplete-item').forEach(item => {
            const img = item.querySelector('img.auto-img');
            if(img) img.style.display = 'none';
        });
    });

    observer.observe(autocompleteList, { childList: true, subtree: true });
}

// 3️⃣ Προαιρετικά: αν θέλουμε να ξεμπλοκάρουμε εικόνες όταν πάμε σε άλλη κατηγορία
function resetImages() {
    document.querySelectorAll('#autocompleteList .autocomplete-item img.auto-img').forEach(img=>{
        img.style.display = 'block';
    });
}

// Μπορούμε να καλέσουμε resetImages() όταν αλλάξει category_id

});