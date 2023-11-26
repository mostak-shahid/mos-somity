jQuery(document).ready(function($){
    $(".upload-image input").change(function(e){
        const file = this.files[0];
        var ths = $(this);
        console.log(file);
        if (file){
          let reader = new FileReader();
          reader.onload = function(event){
            console.log(event.target.result);
            ths.siblings().siblings('.preview-image').find('img').attr('src', event.target.result);
          }
          reader.readAsDataURL(file);
        }
      });
});