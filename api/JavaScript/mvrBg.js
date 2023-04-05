function checkTotal(){
    let globi = document.getElementById('globi').value;
    let vinetki = document.getElementById('vignette').value;
    let total = globi + vinetki;
    document.getElementById('total').innerHTML = total;
}