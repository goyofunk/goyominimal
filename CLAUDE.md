# 고요 테마 패밀리 — 개발 기준

## 구조

워드프레스 install 5개(각각 별도 DB·git 저장소): `goyoartdark`, `goyoartlight`, `goyominimal`, `goyofactory`, `goyoonepage`.
각 install = 부모 테마 `goyobase` + 동명의 자식 테마 1개.

- **부모 `goyobase`**: 공통 기능·서브페이지 디자인·공통 커스터마이저. 단독 사용 금지. 함수/에셋핸들/CSS클래스/DOM ID/JS전역/텍스트도메인 접두는 모두 `goyobase` / `goyobase_` / `goyobase-`. theme_mod 키는 중립 `goyo_*`.
- **자식 테마**: 메인페이지 디자인 + 메인페이지 커스터마이저 + 컬러 오버라이드(style.css) + 테마별 기본값 선언.

## 기준(레퍼런스)

- **자식 기준 = `goyominimal`**. 새 자식 테마는 goyominimal을 복제해 시작한다(예전 기준은 goyoartdark였음).
- **마스터 부모 = `goyominimal` install 안의 `goyobase`**. 여기서만 부모를 수정하고 나머지 4개 install로 미러링한다.

### 미러링 절차
```
robocopy <goyominimal>/goyobase <대상>/goyobase /MIR /XD .git
```
대상: goyoartdark·goyoartlight·goyofactory·goyoonepage install의 goyobase. `.git` 은 제외(대상 저장소 보존).

## 테마별 정책

부모 기본값은 **공용(goyominimal) 기준 = light + 유니콘 OFF**. 예외 테마만 자식 `functions.php`에서 명시 override 한다.

| 테마 | color scheme | 유니콘 마우스 효과(메인 히어로) |
|---|---|---|
| goyoartdark | **dark** (명시 override) | **ON** (명시 override) |
| goyoartlight | light | **ON** (명시 override) |
| goyominimal | light | OFF |
| goyofactory | light | OFF |
| goyoonepage | light | OFF |

- **스킴**: `goyoartdark` 만 dark. goyoartdark `functions.php` 가 `goyo_theme_profile` 필터로 `scheme=dark, bg=#000000` 선언. 나머지는 부모 기본 light.
- **유니콘**: `goyoartdark` · `goyoartlight` 만 사용. 두 테마가 `goyo_theme_defaults` 로 `unicorn_enabled=true, hero_scroll_scale_enabled=true` 선언. 나머지 3개는 `false`(부모 기본도 false).

## 필터 계층(부모 `inc/theme-profile.php`)

- `goyo_theme_profile` → `['scheme'=>dark|light, 'bg'=>'#...']`. 페이지전환 크리티컬 `<head>`·홈 인라인 CSS 가 사용.
- `goyo_default( $key, $fallback )` → 자식이 `goyo_theme_defaults` 필터로 선언한 값 우선, 없으면 부모 폴백. theme_mod 가 저장돼 있으면 그게 최우선(=설정한 사이트엔 기본값 무영향).

## theme_mod 키 호환

부모는 중립 `goyo_*` 키만 읽는다. 구버전 키(`goyoartdark_*` 등) → `goyo_*` 1회 마이그레이션은 `inc/mod-compat.php`. 이 파일의 레거시 접두사 리터럴 `'goyoartdark_'` 는 **과거 DB 데이터 조회용이므로 절대 개명하지 말 것.**

## 코드 규칙

CSS/PHP 작성 규칙은 `.cursor/rules/project-rules.mdc` 참고(클래스 한 줄·font 우선 순서·@media 중괄호 등).
