(function ($) {
    var $html = $("html"),
        $body = $("body"),
        $menuBtn = $(".menu-btn"),
        $menuContainer = $(".menu-container"),
        $overlay = $(".menu-container .overlay"),
        $header = $("header");



    $(document).ready(function () {
        // mbYTPlayer 워터마크 제거 - 프론트 페이지(유튜브 사용 시)에서만 실행
        if (typeof goyoTheme !== "undefined" && goyoTheme.isFrontPage) {
            function removeMbYTPlayerWatermark() {
                $("[class*='ytp_wm_']").remove();
            }
            removeMbYTPlayerWatermark();
            var watermarkObserver = new MutationObserver(function (mutations) {
                mutations.forEach(function (mutation) {
                    mutation.addedNodes.forEach(function (node) {
                        if (node.nodeType === 1 && node.classList && node.classList.toString().indexOf("ytp_wm_") >= 0) {
                            node.remove();
                        }
                        if (node.querySelectorAll) {
                            $(node).find("[class*='ytp_wm_']").remove();
                        }
                    });
                });
            });
            watermarkObserver.observe(document.body, { childList: true, subtree: true });
            setInterval(removeMbYTPlayerWatermark, 2000);
        }

        // 헤더 스크롤 이벤트 (마우스 이동 이후, 위로 스크롤 시 부드럽게 노출)
        if ($header.length) {
            var lastScrollTop = Math.max(window.pageYOffset || 0, 0);
            var lastMouseY = null;
            var minScrollDelta = 4;
            var minMouseDelta = 4;
            var hideStartOffset = 80;
            var lastScrollEventTime = 0;
            var mouseRevealDelayAfterScroll = 140;
            var isScrollTicking = false;

            function showHeader() {
                $header.addClass("header-scroll-visible").removeClass("header-scroll-hidden");
            }

            function hideHeader() {
                $header.addClass("header-scroll-hidden").removeClass("header-scroll-visible");
            }

            function updateHeaderByScroll() {
                var currentScrollTop = Math.max(window.pageYOffset || 0, 0);
                var delta = currentScrollTop - lastScrollTop;
                var shouldUseStickyStyle = currentScrollTop >= 10;
                $header.toggleClass("scActive", shouldUseStickyStyle);

                if (!shouldUseStickyStyle || currentScrollTop <= hideStartOffset) {
                    showHeader();
                    lastScrollTop = currentScrollTop;
                    return;
                }

                if (delta >= minScrollDelta) {
                    hideHeader();
                } else if (delta <= -minScrollDelta) {
                    showHeader();
                }

                lastScrollTop = currentScrollTop;
            }

            function requestScrollHeaderUpdate() {
                lastScrollEventTime = Date.now();
                if (isScrollTicking) {
                    return;
                }
                isScrollTicking = true;
                window.requestAnimationFrame(function () {
                    updateHeaderByScroll();
                    isScrollTicking = false;
                });
            }

            $(window).on("mousemove", function (event) {
                if (lastMouseY === null) {
                    lastMouseY = event.clientY;
                    return;
                }

                var mouseDelta = event.clientY - lastMouseY;
                if (Date.now() - lastScrollEventTime < mouseRevealDelayAfterScroll) {
                    lastMouseY = event.clientY;
                    return;
                }
                if (mouseDelta <= -minMouseDelta) {
                    showHeader();
                }
                lastMouseY = event.clientY;
            });

            $(window).on("scroll", requestScrollHeaderUpdate);
            requestScrollHeaderUpdate();
        }



        // 서브메뉴 드롭다운
        if (!$('.menu-item-has-children').hasClass('show')) {
            $('.current-menu-parent').addClass('show');
        }

        // 모바일 서브메뉴 토글 버튼 추가 및 이벤트 처리
        if ($(".more-navigation").find(".menu-item-has-children").length) {
            $(".menu-item-has-children").prepend('<div class="toggle-btn"></div>');
            $(".menu-item-has-children .toggle-btn").click(function () {
                $(this).toggleClass("show");
                $(this).parent(".menu-item-has-children").toggleClass("show");
            });
        }

        // 메뉴 버튼 클릭 이벤트
        $menuBtn.click(function () {
            $(this).toggleClass("active");
            var isActive = $(this).hasClass("active");
            
            $("html, body").css({ overflow: isActive ? "hidden" : "" });
            $menuContainer.toggleClass("active", isActive);
            $overlay.toggleClass("active", isActive);
        });

    

        // 오버레이 클릭 시 메뉴 닫기
        $overlay.click(function () {
            $menuContainer.removeClass("active");
            $menuBtn.removeClass("active");
            $overlay.removeClass("active");
            $("html, body").css({ overflow: "" });
        });

        // 헤더 검색( inc/blocks.php .search-box ): 돋보기 → 폼 열기/닫기, 외부 클릭·Esc 로 닫기, 입력창 포커스
        (function goyoartdarkInitHeaderSearch() {
            var searchOpenLabel = "검색 열기";
            var searchCloseLabel = "검색 닫기";
            $body.find(".search-box .search-toggle").each(function () {
                var $btn = $(this);
                if (!$btn.attr("type")) {
                    $btn.attr("type", "button");
                }
                if ($btn.attr("aria-expanded") === undefined) {
                    $btn.attr("aria-expanded", "false");
                }
            });
            $body.on("click", ".search-toggle", function (e) {
                e.preventDefault();
                e.stopPropagation();
                var $form = $(this).closest(".search-box").find(".search-form");
                if (!$form.length) {
                    return;
                }
                var willOpen = !$form.hasClass("active");
                $(".search-box .search-form.active").not($form).removeClass("active");
                $(".search-box .search-toggle").not(this).attr("aria-expanded", "false").attr("aria-label", searchOpenLabel);
                $form.toggleClass("active", willOpen);
                $(this).attr("aria-expanded", willOpen ? "true" : "false").attr("aria-label", willOpen ? searchCloseLabel : searchOpenLabel);
                if (willOpen) {
                    var $field = $form.find(".search-field");
                    if ($field.length) {
                        setTimeout(function () {
                            $field.trigger("focus");
                        }, 100);
                    }
                }
            });
            $body.on("click", function (e) {
                if (!$(e.target).closest(".search-box").length) {
                    $(".search-form.active").removeClass("active");
                    $(".search-box .search-toggle").attr("aria-expanded", "false").attr("aria-label", searchOpenLabel);
                }
            });
            $body.on("keydown", function (e) {
                if (e.key !== "Escape" && e.keyCode !== 27) {
                    return;
                }
                if ($(".search-form.active").length) {
                    $(".search-form.active").removeClass("active");
                    $(".search-box .search-toggle").attr("aria-expanded", "false").attr("aria-label", searchOpenLabel);
                }
            });
        })();

        // 맨 위로: foot 스니펫이 wp_footer(늦은 우선순위)에 있어 .gotoTop 이 스크립트 이후에 그려짐 → 위임
        $body.on("click", ".gotoTop", function (e) {
            e.preventDefault();
            // CSS scroll-behavior: smooth 가 전역 적용된 경우 jQuery animate 와 충돌하므로
            // 애니메이션 동안 일시적으로 auto 로 전환했다가 원복한다.
            var scrollRoot = document.scrollingElement || document.documentElement || document.body;
            var $scrollTarget = $(scrollRoot);
            var originalBehavior = $html.css("scroll-behavior");
            $html.css("scroll-behavior", "auto");
            $scrollTarget.stop(true, false).animate({ scrollTop: 0 }, 600, "swing", function () {
                $html.css("scroll-behavior", originalBehavior || "");
            });
        });

        // faqWrapper 아코디언 (고객 편집용 FAQ 패턴)
        $(".faqWrapper").each(function () {
            var $wrapper = $(this);
            $wrapper.find(".faqItem").each(function () {
                var $item = $(this);
                var $header = $item.find(".faq-header");
                var $content = $item.find(".faq-content");
                if ($header.length && $content.length) {
                    if (!$header.find(".faq-icon").length) {
                        $header.append('<span class="faq-icon"></span>');
                    }
                    $header.off("click.faqAccordion").on("click.faqAccordion", function () {
                        if ($item.hasClass("active")) {
                            // 현재 항목이 열려있으면 닫기
                            $content.css("max-height", $content[0].scrollHeight + "px");
                            $content[0].offsetHeight;
                            $item.removeClass("active");
                            $content.css("max-height", "0");
                        } else {
                            // 다른 열린 항목들 먼저 닫기
                            $wrapper.find(".faqItem.active").each(function() {
                                var $otherItem = $(this);
                                var $otherContent = $otherItem.find(".faq-content");
                                $otherContent.css("max-height", $otherContent[0].scrollHeight + "px");
                                $otherContent[0].offsetHeight;
                                $otherItem.removeClass("active");
                                $otherContent.css("max-height", "0");
                            });
                            
                            // 현재 항목 열기
                            $item.addClass("active");
                            $content.css("transition", "none").css("max-height", "9999px");
                            var targetHeight = $content[0].scrollHeight;
                            $content.css("max-height", "0");
                            $content[0].offsetHeight;
                            $content.css("transition", "").css("max-height", targetHeight + "px");
                            $content.one("transitionend", function () {
                                $content.css("max-height", "none");
                            });
                        }
                    });
                }
            });
        });

        // 서브배너 배경 패럴랙스: ::before 레이어를 scale+translate 하여 가장자리 끊김 방지
        (function goyoartdarkInitSubBannerPointerParallax() {
            var PARALLAX_SELECTOR = ".page main > .subBanner, .category main > .subBanner, .single main > .subBanner, .archive main > .subBanner, .search main > .subBanner";
            var PARALLAX_EASING = 0.05;
            var SCROLL_SCALE_MAX = 0.2;
            var SCROLL_SCALE_RANGE = 900;

            var prefersReducedMotion = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
            var isTouchLikeDevice = window.matchMedia && window.matchMedia("(hover: none), (pointer: coarse)").matches;
            if (prefersReducedMotion || isTouchLikeDevice) {
                return;
            }

            var $subBanners = $(PARALLAX_SELECTOR);
            if (!$subBanners.length) {
                return;
            }

            var bannerStates = [];
            $subBanners.each(function () {
                var banner = this;
                var computedBackgroundImage = window.getComputedStyle(banner).backgroundImage;
                if (!computedBackgroundImage || computedBackgroundImage === "none") {
                    return;
                }

                banner.style.setProperty("--subbanner-bg-image", computedBackgroundImage);

                bannerStates.push({
                    element: banner,
                    currentX: 0,
                    currentY: 0,
                    targetX: 0,
                    targetY: 0
                });
            });

            if (!bannerStates.length) {
                return;
            }

            document.documentElement.classList.add("goyo-subbanner-parallax-enabled");
            
            function getResponsiveParallaxConfig() {
                var viewportWidth = window.innerWidth || 1920;
                var viewportHeight = window.innerHeight || 1080;
                var minViewportSide = Math.min(viewportWidth, viewportHeight);

                // 화면이 클수록 이동량/스케일을 키우고, 작은 화면은 은은하게 제한한다.
                if (viewportWidth >= 1680) {
                    return { maxOffsetX: 30, maxOffsetY: 22, scale: 1.1, edgePadding: 32 };
                }
                if (viewportWidth >= 1366) {
                    return { maxOffsetX: 26, maxOffsetY: 19, scale: 1.085, edgePadding: 28 };
                }
                if (viewportWidth >= 1024) {
                    return { maxOffsetX: 21, maxOffsetY: 15, scale: 1.075, edgePadding: 24 };
                }
                if (viewportWidth >= 820) {
                    return { maxOffsetX: 16, maxOffsetY: 12, scale: 1.06, edgePadding: 20 };
                }

                var compactScale = minViewportSide < 420 ? 1.045 : 1.05;
                return { maxOffsetX: 11, maxOffsetY: 8, scale: compactScale, edgePadding: 16 };
            }

            var parallaxConfig = getResponsiveParallaxConfig();

            function applyParallaxConfigToBanners() {
                bannerStates.forEach(function (state) {
                    state.element.style.setProperty("--subbanner-parallax-scale", String(parallaxConfig.scale));
                    state.element.style.setProperty("--subbanner-parallax-edge-padding", parallaxConfig.edgePadding + "px");
                });
            }

            // 스크롤 진행량에 따라 배경 이미지를 아주 미세하게 확대한다.
            function updateScrollScale() {
                var currentScrollTop = Math.max(window.pageYOffset || 0, 0);
                var scrollProgress = Math.min(currentScrollTop / SCROLL_SCALE_RANGE, 1);
                var scrollScale = (scrollProgress * SCROLL_SCALE_MAX).toFixed(4);
                bannerStates.forEach(function (state) {
                    state.element.style.setProperty("--subbanner-scroll-scale", String(scrollScale));
                });
            }

            applyParallaxConfigToBanners();
            updateScrollScale();

            var isTicking = false;
            var hasMouseMoved = false;

            function applyParallaxFrame() {
                var hasActiveDelta = false;
                bannerStates.forEach(function (state) {
                    state.currentX += (state.targetX - state.currentX) * PARALLAX_EASING;
                    state.currentY += (state.targetY - state.currentY) * PARALLAX_EASING;

                    var isSettled = Math.abs(state.targetX - state.currentX) < 0.01 && Math.abs(state.targetY - state.currentY) < 0.01;
                    if (!isSettled) {
                        hasActiveDelta = true;
                    }

                    state.element.style.setProperty("--subbanner-parallax-x", state.currentX.toFixed(2) + "px");
                    state.element.style.setProperty("--subbanner-parallax-y", state.currentY.toFixed(2) + "px");
                });

                if (hasActiveDelta || hasMouseMoved) {
                    hasMouseMoved = false;
                    window.requestAnimationFrame(applyParallaxFrame);
                    return;
                }

                isTicking = false;
            }

            function requestParallaxFrame() {
                if (isTicking) {
                    return;
                }
                isTicking = true;
                window.requestAnimationFrame(applyParallaxFrame);
            }

            function updateTargetByPointer(clientX, clientY) {
                var viewportCenterX = window.innerWidth * 0.5;
                var viewportCenterY = window.innerHeight * 0.5;
                var normalizedX = (clientX - viewportCenterX) / viewportCenterX;
                var normalizedY = (clientY - viewportCenterY) / viewportCenterY;
                var clampedX = Math.max(-1, Math.min(1, normalizedX));
                var clampedY = Math.max(-1, Math.min(1, normalizedY));
                var nextTargetX = clampedX * parallaxConfig.maxOffsetX;
                var nextTargetY = clampedY * parallaxConfig.maxOffsetY;

                bannerStates.forEach(function (state) {
                    state.targetX = nextTargetX;
                    state.targetY = nextTargetY;
                });
                hasMouseMoved = true;
                requestParallaxFrame();
            }

            $(window).on("mousemove.subBannerParallax", function (event) {
                updateTargetByPointer(event.clientX, event.clientY);
            });

            $(window).on("mouseleave.subBannerParallax", function () {
                bannerStates.forEach(function (state) {
                    state.targetX = 0;
                    state.targetY = 0;
                });
                requestParallaxFrame();
            });

            $(window).on("resize.subBannerParallax", function () {
                parallaxConfig = getResponsiveParallaxConfig();
                applyParallaxConfigToBanners();
                updateScrollScale();
            });

            $(window).on("scroll.subBannerParallaxScale", function () {
                updateScrollScale();
            });
        })();

        /* 홈 3열 가로 스와이프 — 한 줄 개수는 data-goyo-per-row(커스터마이저) */
        function goyoartdarkInitMainTrioSwiper() {
            var $wrap = $(".goyo-main-trio-swiper");
            if (!$wrap.length || typeof Swiper === "undefined") {
                return;
            }
            var el = $wrap.get(0);
            var prevT = $wrap.data("goyoTrioSwiperInstance");
            if (prevT && typeof prevT.destroy === "function") {
                prevT.destroy(true, true);
                $wrap.removeData("goyoTrioSwiperInstance");
            } else if (el && el.swiper && typeof el.swiper.destroy === "function") {
                el.swiper.destroy(true, true);
            }
            var n = $wrap.find(".swiper-slide").length;
            if (n < 1) {
                return;
            }
            var rawPer = $wrap.attr("data-goyo-per-row");
            var perRow = parseInt(rawPer, 10);
            if (!perRow || perRow < 1 || perRow > 5) {
                perRow = 3;
            }
            /* Swiper 4 breakpoints 는 min-width — 기본값이 가장 좁은 구간에 적용되므로 예전처럼 perRow(기본 3)를 둔다 */
            var bp600 = Math.min(2, perRow);
            $wrap.data(
                "goyoTrioSwiperInstance",
                new Swiper(el, {
                    slidesPerView: perRow,
                    spaceBetween: 20,
                    loop: true,
                    observer: true,
                    observeParents: true,
                    speed: 800,
                    autoplay: {
                        delay: 3000,
                        disableOnInteraction: false,
                    },
                    pagination: { el: $wrap.find(".swiper-pagination").get(0), clickable: true },
                    navigation: {
                        nextEl: $wrap.find(".swiper-button-next").get(0),
                        prevEl: $wrap.find(".swiper-button-prev").get(0),
                    },
                    breakpoints: {
                        600: { slidesPerView: bp600, spaceBetween: 12 },
                        1024: { slidesPerView: perRow, spaceBetween: 14 },
                    },
                })
            );
        }
        goyoartdarkInitMainTrioSwiper();
        if (typeof wp !== "undefined" && wp.customize && wp.customize.selectiveRefresh) {
            wp.customize.selectiveRefresh.bind("partial-content-rendered", function (placement) {
                var $c = placement && placement.container ? $(placement.container) : $();
                if ($c.find(".goyo-main-trio-swiper").length) {
                    goyoartdarkInitMainTrioSwiper();
                }
            });
        }

    });
})(jQuery);
