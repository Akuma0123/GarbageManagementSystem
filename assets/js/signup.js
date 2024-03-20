var signUp = true;
function toggleDisplay(){
  if(signUp){
    document.getElementById("re-password").style.display = "none";
    document.getElementById("email").style.display = "none";
    
    
    document.getElementById("toggle").innerHTML = "sign up";
  }else{
    document.getElementById("re-password").style.display = "block";
    document.getElementById("email").style.display = "block";
    document.getElementById("button").innerHTML = "sign up";
    
  }
  signUp = !(signUp);
}