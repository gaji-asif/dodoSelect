let selectedFile;
$('#form-import #file').change(function(event){
    selectedFile = event.target.files[0];
});

let file=[{
    "name":"jayanth",
    "data":"scd",
    "abc":"sdef"
}]

var data = {
    data: [],
    shipper: ''
};
$('#BtnImportSubmit').on('click', function(){
    XLSX.utils.json_to_sheet(file, 'out.xlsx');
    if(selectedFile){
        let fileReader = new FileReader();
        fileReader.readAsBinaryString(selectedFile);
        fileReader.onload = (event)=>{
           let file = event.target.result;
           let workbook = XLSX.read(file,{type:"binary"});
           // console.log(workbook);
           workbook.SheetNames.forEach(sheet => {
              let rowObject = XLSX.utils.sheet_to_row_object_array(workbook.Sheets[sheet]);
              data.data.push(rowObject);
            });

        // console.log($('#form-import input[type=file]').val());
        let url = $('#form-import').attr('action')+'?_token='+$('meta[name=csrf-token]').attr('content');
        data.shipper = $('#form-import select[name=shipper]').val();
        $.ajax({
            url: url,
            type: 'post',
            data: data,
        }).done(function(response){
            $('#datatable').DataTable().ajax.reload();
            alert('Data successfully added');
        });
        }
    }
});