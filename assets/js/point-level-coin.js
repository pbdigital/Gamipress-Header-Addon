jQuery('document').ready(function($){
    $('.header-aside').prepend(`<div style="display: flex;align-items: center;margin-right: 35px" class="gamification">
                                        <div style="width: 50px;height: 50px;display: flex;justify-content: center;align-items: center;border-radius: 50%;background: ${point_level_vars.buddy_theme_accent_color}" class="trophy">
                                        <img src="${point_level_vars.rank_img}" style="width: 50px">
                                        </div>
                                        <div style="display: flex;flex-direction: column;min-width: 200px;padding-left: 15px;">
                                        <div style="display:flex; flex-direction: row;justify-content: space-between;align-items: center">
                                            <div style="font-size: 15px;">
                                                Level ${point_level_vars.current_rank}
                                            </div>
                                            <div style="font-size: 12px">
                                                <span id="points">${point_level_vars.current_points}</span> / ${point_level_vars.points_needed}
                                            </div>
                                        </div>
                                        <div style="padding-top: 5px">
                                            <div style="width:100%;height: 8px;background-color: #EBF0F4;border-radius: 10px ">
                                                <div class="progress_bar" style="width:${point_level_vars.completion}%;height: 8px;border-radius: 10px; background: ${point_level_vars.buddy_theme_accent_color};?>">
                                                </div>
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                    <a style="display: flex;align-items: center; margin-right: 35px" href="${point_level_vars.$redeem_screen}" data-balloon-pos="down" data-balloon="Earn coins to unlock goodies" class="coins">
                                        <div style="display: flex;justify-content: center;align-items: center;border-radius: 50%;">
                                        <img src="${point_level_vars.coins_img}" style="max-width:40px;border:none;">
                                        </div>
                                        <div style="font-size: 16px;padding-left: 10px;font-weight: bold;" >
                                           ${point_level_vars.current_coins}
                                        </div>
                                    </a>`);
 });