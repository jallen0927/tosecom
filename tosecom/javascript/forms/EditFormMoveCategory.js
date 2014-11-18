/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

;(function($){
    $.entwine('ss', function($){
        
        var _$modCategoryFields = $(".cms-edit-form .mod-category-fields");
        var _$modCategoryMoveSub = $(".cms-edit-form .mod-category-fields #moveSub");
        var _$modOptionMoveReal = $(".cms-edit-form .mod-category-fields #Form_ItemEditForm_modOptions");
        var _$modCategoryConfirm = $(".cms-edit-form .Actions .mod-category-confirm");
        var _$modCategorySave = $(".cms-edit-form .Actions #Form_ItemEditForm_action_doSave");
        var _$modCategoryDelete = $(".cms-edit-form .Actions .action-delete-mod, .cms-edit-form .Actions .action-delete");
        var _$modCategoryCancel = $(".cms-edit-form .mod-category-cancel");
        
        $(".cms-edit-form .Actions .action-delete-mod").click(function(){
            
            if(_$modCategoryFields.get(0)){
//                console.log(_$modOptionMove.hasClass("result-selected")); return false;
                _$modCategoryFields.show();
                _$modCategoryConfirm.show();
                _$modCategoryCancel.show();
                _$modCategorySave.hide();
                _$modCategoryDelete.hide();
                
                checkOptionMove();
                
                return false;
            }
            
        });
        
        _$modCategoryCancel.click(function(){
                _$modCategoryFields.hide();
                _$modCategoryConfirm.hide();
                _$modCategoryCancel.hide();
                _$modCategorySave.show();
                _$modCategoryDelete.show();
        });
        
        function checkOptionMove() {
//            console.log(_$modOptionMove.hasClass("result-selected"));
//            console.log(typeof(_$modOptionMoveReal.val()));
            if(_$modOptionMoveReal.val()==='1'){
                _$modCategoryMoveSub.show();
            } else {
                _$modCategoryMoveSub.hide();
            }
        }
        
        _$modOptionMoveReal.change(function(){
            checkOptionMove();
        });
        
//            $(".cms-edit-form .Actions button.action").entwine({
//                    /**
//                     * Function: onclick
//                     */
//                    onclick: function(e) {
//                            // Confirmation on delete. 
//                            if(
//                                    this.hasClass('action-move-category')
//                                    && !confirm(ss.i18n._t('TABLEFIELD.DELETECONFIRMMESSAGE'))
//                            ) {
//                                    e.preventDefault();
//                                    return false;
//                            }
//
//                            return false;
//                    }
//            });   
            
    });
    
})(jQuery);