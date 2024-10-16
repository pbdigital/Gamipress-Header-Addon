(function($) {
    'use strict';

    // Constants for CSS classes and selectors
    const HEADER_ASIDE_SELECTOR = '.header-aside';
    const COINS_CLASS = 'coins';
    const GAMIFICATION_CLASS = 'gamification';

    // Helper functions
    const createCoinsElement = (vars) => {
        return `
            <a style="display: flex; align-items: center; margin-right: 35px" href="${vars.redeem_screen}" data-balloon-pos="down" data-balloon="Earn coins to unlock goodies" class="${COINS_CLASS}">
                <div style="display: flex; justify-content: center; align-items: center; border-radius: 50%;">
                    <img src="${vars.coins_img}" style="max-width:40px; border:none;" alt="Coins">
                </div>
                <div style="font-size: 16px; padding-left: 10px; font-weight: bold;">
                    ${vars.current_coins}
                </div>
            </a>
        `;
    };

    const createGamificationElement = (vars) => {
        return `
            <div style="display: flex; align-items: center; margin-right: 35px" class="${GAMIFICATION_CLASS}">
                <div style="width: 50px; height: 50px; display: flex; justify-content: center; align-items: center; border-radius: 50%; background: ${vars.buddy_theme_accent_color}" class="trophy">
                    <img src="${vars.rank_img}" style="width: 50px" alt="Rank">
                </div>
                <div style="display: flex; flex-direction: column; min-width: 200px; padding-left: 15px;">
                    <div style="display:flex; flex-direction: row; justify-content: space-between; align-items: center">
                        <div style="font-size: 15px;">
                            ${vars.current_rank}
                        </div>
                        <div style="font-size: 12px">
                            <span id="points">${vars.current_points}</span> / ${vars.points_needed}
                        </div>
                    </div>
                    <div style="padding-top: 5px">
                        <div style="width:100%; height: 8px; background-color: #EBF0F4; border-radius: 10px">
                            <div class="progress_bar" style="width:${vars.completion}%; height: 8px; border-radius: 10px; background: ${vars.buddy_theme_accent_color};">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    };

    // Main function
    const initPointLevelCoin = () => {
        if (typeof point_level_vars === 'undefined') {
            console.error('point_level_vars is not defined');
            return;
        }

        const $headerAside = $(HEADER_ASIDE_SELECTOR);
        if ($headerAside.length === 0) {
            console.error('Header aside element not found');
            return;
        }

        if (point_level_vars.redeem_screen && point_level_vars.coins_img && point_level_vars.current_coins) {
            $headerAside.prepend(createCoinsElement(point_level_vars));
        }

        if (point_level_vars.rank_img && point_level_vars.points_needed && point_level_vars.current_rank) {
            $headerAside.prepend(createGamificationElement(point_level_vars));
        }
    };

    // Initialize on document ready
    $(document).ready(initPointLevelCoin);

})(jQuery);
