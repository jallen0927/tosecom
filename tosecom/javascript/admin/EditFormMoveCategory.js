/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

;(function($){
    $.entwine('ss', function($){
        
        $(".cms-edit-form .Actions button.action").click(function(){

            if($(this).hasClass('action-move-category')){
                return confirm('move?');
            }
            
            if($(this).hasClass('tose-not-empty', 'action-delete')){
                return alert('This Category has products or sub-categories, please remove them or move them under another category first');
            }
            
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
