jQuery(function($){
    var wxp_fancybox = {
        buttons : [
            'close'
        ],
        clickSlide : false,
        clickOutside : false,

        dblclickContent : false,
        dblclickSlide   : false,
        dblclickOutside : false,
        afterShow: function(instance,current){
            wxp_passwords.InitSetCheckbox();
        }
    };
    var wxp_passwords = {
        init: function(){
            $(document.body).on('click','.wxp-add-password',{view:this},this.AddPasswords);
            $(document.body).on('click','.wxp-save-pass',{view:this},this.SavePasswords);
            $(document.body).on('click','.icon-wxp-delete',{view:this},this.DeletePasswords);
            $(document.body).on('click','.icon-wxp-edit',{view:this},this.EditPasswords);
            $(document.body).on('click','.wxp-update-pass',{view:this},this.UpdatePasswords);
            $(document.body).on('change','.wxp-permission',{view:this},this.CheckboxChange);
            $(document.body).on('click','.wxp-password-settings',{view:this},this.SettingOpen);
            $(document.body).on('click','.wxp-save-settings',{view:this},this.SaveSettings);
        },
        AddPasswords:function(){
            $.fancybox.open({
                src  : wxp_pass.ajax_url+'?action=wxp_load_add_password',
                type : 'ajax',
                opts : wxp_fancybox,
                dataType: 'json'
            });
        },
        SavePasswords:function(){
            var form = $('#wxp-pass-frm').serialize();
            wxp_passwords.InitAjax(
                {
                    'wxp-data': form,
                    'case':'save-wxp-password'
                });
        },
        DeletePasswords:function(){
            wxp_passwords.WxpBlockUi('table.wxp-passwords');
            $.ajax({
                type	: "POST",
                cache	: false,
                url     : wxp_pass.ajax_url,
                dataType : 'json',
                data: {
                    'action':'wxp_passwords_store',
                    'data': {
                        'wxp-pass-id': $(this).attr('data-id'),
                        'case':'delete-wxp-password'
                    }
                },
                success: function(res){
                    wxp_passwords.WxpreloadEl();
                    wxp_passwords.WxpUnBlockUi('table.wxp-passwords');
                }
            });
        },
        EditPasswords:function(){
            $.fancybox.open({
                src  : wxp_pass.ajax_url+'?action=wxp_load_edit_password&id='+$(this).attr('data-id'),
                type : 'ajax',
                opts : wxp_fancybox,
                dataType: 'json'
            });
        },
        UpdatePasswords:function(){
            var form = $('#wxp-pass-frm').serialize();
            wxp_passwords.InitAjax(
                {
                    'wxp-data': form,
                    'case':'update-wxp-password'
                });
        },
        InitAjax:function(wp_pass_data){
            wxp_passwords.WxpBlockUi('.wxp-pass-box');
            $.ajax({
                type	: "POST",
                cache	: false,
                url     : wxp_pass.ajax_url,
                dataType : 'json',
                data: {
                    'action':'wxp_passwords_store',
                    'data': wp_pass_data
                },
                success: function(res){
                    wxp_passwords.WxpUnBlockUi('.wxp-pass-box');
                    wxp_passwords.ShowMsg(res.msg,res.class);
                    if(res.reload){
                        wxp_passwords.WxpBlockUi('table.wxp-passwords');
                        wxp_passwords.WxpreloadEl();
                        wxp_passwords.WxpUnBlockUi('table.wxp-passwords');
                    }
                }
            });
        },
        ShowMsg:function(msg,cls){
            if(msg!=''){
                $('.wxp-pass-msg').attr('class','wxp-pass-msg');
                $('.wxp-pass-msg').addClass(cls).html(msg);
            }
        },
        WxpBlockUi:function(ele){
            $(ele).block({ message: '',overlayCSS: { backgroundColor: '#ddd'},css: {'border':'none','background':'none'}  });
        },
        WxpUnBlockUi:function(ele){
            $(ele).unblock();
        },
        WxpreloadEl:function(el){
            wxp_passwords.WxpreloadTab();
            setTimeout(function(){
                $.fancybox.close();
            },2000);
        },
        WxpreloadTab:function(){
            $.get(window.location.href,function(data,status){
                var $response = $('<div />').html(data);
                var content = $response.find('table.wxp-passwords').html();
                $('table.wxp-passwords').html(content);
            });
        },
        CheckboxChange:function(){
            var id = $(this).closest("li").attr('id');
            var value = $(this).val();
            var $this = $(this);
            wxp_passwords.WxpSetCheckbox(id,value,$this);
        },
        InitSetCheckbox:function(){
            $(document).find('#wxp-pass-frm input:checkbox').each(function(){
                var id = $(this).closest("li").attr('id');
                var value = $(this).val();
                var $this = $(this);
                wxp_passwords.WxpSetCheckbox(id,value,$this);
            });
        },
        WxpSetCheckbox:function(id,value,$this){
            if(id==='wxp_a_id_'+value){
                if($this.is(':checked')){

                    $('#wxp_b_id_'+value).find("input:checkbox").attr("disabled", true);
                }
                else
                {

                    $('#wxp_b_id_'+value).find("input:checkbox").attr("disabled", false);
                }
            }
            else if(id==='wxp_b_id_'+value){
                if($this.is(':checked')){
                    $('#wxp_a_id_'+value).find("input:checkbox").attr("disabled", true);
                }
                else
                {
                    $('#wxp_a_id_'+value).find("input:checkbox").attr("disabled", false);
                }
            }
        },
        SettingOpen:function(){
            $.fancybox.open({
                src  : wxp_pass.ajax_url+'?action=wxp_load_settings',
                type : 'ajax',
                opts : wxp_fancybox,
                dataType: 'json'
            });
        },
        SaveSettings:function(){
            var form = $('#wxp-pass-frm').serialize();
            wxp_passwords.InitAjax(
                {
                    'wxp-data': form,
                    'case':'save-wxp-settings'
                });
        }
    };
    wxp_passwords.init();
});