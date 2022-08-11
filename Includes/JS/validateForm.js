function validateForm() {
    var email = document.forms["form"]["email"].value;
    var goodEmail = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
        if(!email.match(goodEmail)) {
            alert("Please enter a valid email");
        return false;
        }
        var passwrd =  document.forms["form"]["password"].value;
        var goodPassword=  /^(?=.*[0-9])(?=.*[!@#$%^&*])[a-zA-Z0-9!@#$%^&*]{7,15}$/;
        if(!passwrd.match(goodPassword)){
            alert("Password must be between 7-15 characters, contain at least one numeric digit and one special character");
            return false;
        }
        alert("test");
        return false;
}