const plugin = require('tailwindcss/plugin');

module.exports = {
  mode: 'jit',
  content: [
    './woocommerce/**/*.php',
    './**/*.php',
    './assets/js/**/*.js',
  ],
  theme: {
    extend: {
      /* -----------------------------------
       * Typography – families
       * --------------------------------- */
      fontFamily: {
        primary: ['Inter', 'system-ui', 'sans-serif'],
        secondary: ['Menlo', 'ui-monospace', 'SFMono-Regular', 'monospace'],
        lemondecourrierstd: ['"Le Monde Courrier Std"', 'serif'],

      },

      /* -----------------------------------
       * Colors – existing system
       * --------------------------------- */
      /* -------------------------------------------------
       * Colors
       * ----------------------------------------------- */
      colors: {
        primary: {
          DEFAULT: '#7ED0E0',
          sky: '#C6ECF4',
          light: '#F1F8F9',
          dark: '#024B79',
          darker: '#003455',
          olive: '#5F604B',
        },
        secondary: {
          DEFAULT: '#FF8866',
          light: '#FF8866',
          dark: '#2C2C21',
          darker: '#1E244B',
          200: '#F0C5A5',
          100: '#F8E2D2',
          50: '#FBF0E8',
        },
        tertiary: {
          DEFAULT: '#5F7176',
        },
        background: {
          DEFAULT: '#ffffff',
          light: '#5F604B',
          dark: '#7ED0E0',
        },
        neutral: {
          DEFAULT: '#101828',
          800: '#1D2939',
          700: '#344054',
          600: '#475467',
          500: '#667085',
          400: '#98A2B3',
          300: '#D0D5DD',
          200: '#EAECF0',
          100: '#F2F4F7',
          50: '#F9FAFB',
          25: '#FCFCFD',
        },
        highlight: {
          primary: '#101828',
          secondary: '#041227',
          light: '#FFF9E2',
        },
        text: {
          primary: '#1D2939',
          secondary: '#ffffff',
          accent: '#1D2939',
        },
        hover: {
          bg: '#F6F6F6',
          text: '#000000',
        },

        // Tailwind slate subset used in the Figma tokens
        slate: {
          200: 'rgba(226, 232, 240, 1)',
          600: 'rgba(71, 85, 105, 1)',
          900: 'rgba(15, 23, 42, 1)',
        },

        // St Patrick’s brand namespace (simplified)
        stp: {
          primaryBlue: 'rgba(126, 208, 224, 1)',      // StPatricks_Primary_Blue
          primaryDarkOlive: 'rgba(95, 96, 75, 1)',    // StPatricks_Primary_Dark_Olive_Green
          auxDarkBg: 'rgba(0, 0, 0, 1)',              // StPatricks_Aux_DarkBG

          grey: {
            50: 'rgba(136, 138, 117, 1)',
            65: 'rgba(161, 161, 170, 1)',
            94: 'rgba(229, 247, 250, 1)',
            95: 'rgba(238, 245, 246, 1)',
            98: 'rgba(252, 251, 248, 1)',
          },
          yellow: {
            20: 'rgba(56, 57, 45, 1)',
            33: 'rgba(93, 94, 74, 1)',
          },
          red: {
            50: 'rgba(255, 1, 5, 1)',
            66: 'rgba(222, 140, 115, 1)',
          },
          orange: {
            50: 'rgba(254, 123, 2, 1)',
            69: 'rgba(255, 142, 99, 1)',
          },
          rose: {
            75: 'rgba(255, 126, 176, 1)',
          },
          blue: {
            65: 'rgba(75, 115, 255, 1)',
          },
          magenta: {
            70: 'rgba(255, 102, 244, 1)',
          },
          cyan: {
            75: 'rgba(160, 216, 224, 1)',
            90: 'rgba(222, 235, 237, 1)',
          },
          white: {
            solid: 'rgba(255, 255, 255, 1)',
          },
          black: {
            solid: 'rgba(0, 0, 0, 1)',
          },
        },
      },

      /* -------------------------------------------------
       * Backgrounds / gradients
       * ----------------------------------------------- */
      backgroundImage: {
        'st-patricks':
          'linear-gradient(278deg, #FAFBF6 3.24%, #F1F8F9 90.88%)',
        'stp-aux-darkbg3':
          'linear-gradient(rgba(243, 234, 222, 1) 0%, rgba(241, 243, 222, 1) 100%)',
        'stp-aux-darkbg4':
          'linear-gradient(rgba(246, 237, 224, 1) 0%, rgba(244, 245, 222, 1) 100%)',
      },

      backgroundColor: {
        hover: '#F6F6F6',
      },
      textColor: {
        hover: '#041227',
      },

      /* -------------------------------------------------
       * Semantic font sizes (design tokens)
       * ----------------------------------------------- */
      fontSize: {
        // existing small stuff
        xs: '14px',
        base: '16px',
        wp_editor_p: '20px',

        // Headings
        h1: ['3rem', { letterSpacing: '-0.04rem', lineHeight: '3rem' }],
        h2: ['1.88rem', { letterSpacing: '-0.01rem', lineHeight: '2.25rem' }],
        h3: ['1.5rem', { letterSpacing: '-0.01rem', lineHeight: '2rem' }],
        h4: ['1.25rem', { letterSpacing: '-0.01rem', lineHeight: '1.75rem' }],

        'h1-mobile': ['1.75rem', { letterSpacing: '-0.02rem', lineHeight: '1.75rem' }],
        'h2-mobile': ['1.5rem', { letterSpacing: '-0.01rem', lineHeight: '1.75rem' }],
        'h3-mobile': ['1.25rem', { letterSpacing: '-0.01rem', lineHeight: '1.5rem' }],
        'h4-mobile': ['1.12rem', { letterSpacing: '-0.01rem', lineHeight: '1.42rem' }],

        display: ['3rem', { letterSpacing: '-0.04rem', lineHeight: '3.5rem' }],
        'display-mobile': [
          '2.25rem',
          { letterSpacing: '-0.03rem', lineHeight: '2.5rem' },
        ],

        // Body / UI
        large: ['1.12rem', { letterSpacing: '0rem', lineHeight: '1.75rem' }],
        lead: ['1.25rem', { letterSpacing: '0rem', lineHeight: '1.75rem' }],
        p: ['1rem', { letterSpacing: '0rem', lineHeight: '1.75rem' }],
        'p-ui': ['1rem', { letterSpacing: '0rem', lineHeight: '1.5rem' }],
        'p-ui-medium': ['1rem', { letterSpacing: '0rem', lineHeight: '1.5rem' }],
        list: ['1rem', { letterSpacing: '0rem', lineHeight: '1.5rem' }],

        body: ['0.88rem', { letterSpacing: '0rem', lineHeight: '1.5rem' }],
        'body-medium': ['0.88rem', { letterSpacing: '0rem', lineHeight: '1.5rem' }],

        subtle: ['0.88rem', { letterSpacing: '0rem', lineHeight: '1.25rem' }],
        'subtle-medium': ['0.88rem', { letterSpacing: '0rem', lineHeight: '1.25rem' }],
        'subtle-semibold': ['0.88rem', { letterSpacing: '0rem', lineHeight: '1.25rem' }],

        small: ['0.88rem', { letterSpacing: '0rem', lineHeight: '0.88rem' }],
        detail: ['0.75rem', { letterSpacing: '0rem', lineHeight: '1rem' }],
        badge: ['0.75rem', { letterSpacing: '0rem', lineHeight: '1rem' }],

        blockquote: ['1rem', { letterSpacing: '0rem', lineHeight: '1.5rem' }],
        'inline-code': ['0.88rem', { letterSpacing: '0rem', lineHeight: '1.25rem' }],

        'table-head': ['1rem', { letterSpacing: '0rem', lineHeight: '1.5rem' }],
        'table-item': ['1rem', { letterSpacing: '0rem', lineHeight: '1.5rem' }],

        'kb-shortcut': ['0.75rem', {
          letterSpacing: '0.07rem',
          lineHeight: '1.25rem',
        }],
        code: ['0.88rem', { letterSpacing: '0rem', lineHeight: '1.25rem' }],

        'card-title': ['1.5rem', { letterSpacing: '-0.02rem', lineHeight: '1.5rem' }],
      },

      /* -------------------------------------------------
       * Layout / misc
       * ----------------------------------------------- */
      width: {
        'container-md': '1084px',
        'container-lg': '1280px',
        container: '1280px',
      },
      maxWidth: {
        container: '1280px',
        container_md: '1018px',
        xxs: '320px',
        xs: '480px',
        mob: '575px',
        sm: '640px',
        md: '768px',
        tab: '998px',
        lg: '1200px',
        xl: '1280px',
        xxl: '1440px',
        ultrawide: '1920px',
      },

      borderRadius: {
        custom: '0px',
        'custom-sm': '4px',
        'custom-md': '8px',
        'custom-lg': '16px',
        'custom-xl': '40px',
        'custom-full': '100%',
        btn: '0px',
      },

      animation: {
        scroll300: 'scroll 300s linear infinite',
      },
      keyframes: {
        scroll: {
          '0%': { transform: 'translateX(0)' },
          '100%': { transform: 'translateX(-100%)' },
        },
      },
    },

    /* ---------------------------------------------------
     * Screens / container
     * ------------------------------------------------- */
    screens: {
      xxs: '320px',
      xs: '480px',
      mob: '575px',
      sm: '640px',
      md: '768px',
      tab: '993px',
      ipad: '1084px',
      lg: '1100px',
      xl: '1280px',
      xxl: '1440px',
      ultrawide: '1920px',
    },
    container: {
      center: true,
      padding: '1.5rem',
      screens: {
        xxs: '320px',
        xs: '480px',
        mob: '575px',
        sm: '640px',
        md: '768px',
        lg: '1024px',
        xl: '1280px',
        xxl: '1480px',
        ultrawide: '1920px',
      },
    },
  },

  variants: {
    extend: {
      backgroundColor: ['not-first'],
      display: ['before', 'after'],
    },
  },

  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
    require('tailwindcss-pseudo')({
      empty: true,
      before: true,
      after: true,
    }),
  ],
  safelist: [
    'aspect-[4/3]',
    'left-[-25px]',
    'w-[calc(100%+50px)]',
    'border-t-[6px]',
    'border-b-[6px]',
    'border-r-[6px]',
    'border-l-[6px]',
    'bg-opacity-90',
    'group-hover:bg-[#041227]',
    'group-focus-within:bg-[#041227]',
    'focus-visible:bg-[#041227]',
    'md:col-span-1',
    'md:col-span-2',
    'md:col-span-3',
    'md:col-span-4',
    'md:col-span-6',
    'md:col-span-8',
    'md:col-span-12',
    'lg:col-span-1',
    'lg:col-span-2',
    'lg:col-span-3',
    'lg:col-span-4',
    'lg:col-span-6',
    'lg:col-span-8',
    'lg:col-span-12',
    'col-span-1',
    'col-span-2',
    'col-span-3',
    'col-span-4',
    'col-span-6',
    'col-span-8',
    'col-span-12',
    'hover:bg-hover',
    'hover:text-hover',
    'bg-hover',
    'text-hover',
    'menu-item',
    'current-menu-item',
    'current-menu-ancestor',
    'current-menu-parent',
    'menu-item-has-children',
    'opacity-0',
    'opacity-100',
    'pointer-events-auto',
    'transition-opacity',
    'duration-500',
    'duration-700',
    'absolute',
    'relative',
    'top-0',
    'left-0',
    'right-0',
    'bottom-0',
    'inset-0',
    'max-w-[745px]',
    'fit-content',
    'pt-0',
    'pt-1',
    'pt-2',
    'pt-3',
    'pt-4',
    'pt-5',
    'pb-0',
    'pb-1',
    'pb-2',
    'pb-3',
    'pb-4',
    'pb-5',
    'pl-4',
    'pr-4',
    'pl-5',
    'pr-5',
    'pl-6',
    'pr-6',
    'pl-7',
    'pr-7',
    'pl-8',
    'pr-8',
    'pl-9',
    'pr-9',
    'mb-4',
    'mb-5',
    'mb-6',
    'mb-7',
    'mb-8',
    'pl-10',
    'pr-10',
    'w-1/2',
    'w-1/3',
    'w-2/3',
    'w-1/4',
    'w-2/4',
    'w-3/4',
    'w-1/5',
    'w-2/5',
    'w-3/5',
    'w-4/5',
    'w-1/6',
    'w-2/6',
    'w-3/6',
    'w-4/6',
    'w-5/6',
    'w-1/12',
    'w-2/12',
    'w-3/12',
    'w-4/12',
    'w-5/12',
    'w-6/12',
    'w-7/12',
    'w-8/12',
    'w-9/12',
    'w-10/12',
    'w-11/12',
    'w-full',
    // Container classes
    'container',
    'container-md',
    'max-w-container',
    'max-w-container-md',
    'max-w-container-lg',
    'max-w-xxs',
    'max-w-xs',
    'max-w-mob',
    'max-w-sm',
    'max-w-md',
    'max-w-lg',
    'max-w-xl',
    'max-w-xxl',
    'max-w-ultrawide',
    'rounded-b-none',
    'rounded-l-lg',
    'rounded-r-lg',
    'md:rounded-l-lg',
    'md:rounded-r-lg',
    'rounded-t-lg',
    'md:rounded-t-none',
    'grid-cols-1',
    'grid-cols-2',
    'grid-cols-3',
    'sm:grid-cols-1',
    'sm:grid-cols-2',
    'sm:grid-cols-3',
    'lg:grid-cols-1',
    'lg:grid-cols-2',
    'lg:grid-cols-3',
    'grid-cols-4',
    'grid-cols-5',
    'grid-cols-6',
    'grid-cols-7',
    'grid-cols-8',
    'grid-cols-9',
    'grid-cols-10',
    'grid-cols-11',
    'grid-cols-12',
    'max-sm:grid-cols-1',
    'max-sm:grid-cols-2',
    'max-sm:grid-cols-3',
   
    ...Array.from({ length: 101 }, (_, i) => `p-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `px-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `py-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `pt-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `pb-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `pl-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `pr-[${i + 1}rem]`),
    // Dynamic padding classes devices
    ...Array.from({ length: 101 }, (_, i) => `xs:p-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `xs:px-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `xs:py-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `xs:pt-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `xs:pb-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `xs:pl-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `xs:pr-[${i + 1}rem]`),
    //mob
    ...Array.from({ length: 101 }, (_, i) => `mob:p-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `mob:px-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `mob:py-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `mob:pt-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `mob:pb-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `mob:pl-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `mob:pr-[${i + 1}rem]`),
    //sm
    ...Array.from({ length: 101 }, (_, i) => `sm:p-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `sm:px-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `sm:py-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `sm:pt-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `sm:pb-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `sm:pl-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `sm:pr-[${i + 1}rem]`),
    //md
    ...Array.from({ length: 101 }, (_, i) => `md:p-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `md:px-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `md:py-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `md:pt-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `md:pb-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `md:pl-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `md:pr-[${i + 1}rem]`),
    //lg
    ...Array.from({ length: 101 }, (_, i) => `lg:p-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `lg:px-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `lg:py-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `lg:pt-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `lg:pb-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `lg:pl-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `lg:pr-[${i + 1}rem]`),
    //xl
    ...Array.from({ length: 101 }, (_, i) => `xl:p-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `xl:px-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `xl:py-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `xl:pt-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `xl:pb-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `xl:pl-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `xl:pr-[${i + 1}rem]`),
    //xxl
    ...Array.from({ length: 101 }, (_, i) => `xxl:p-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `xxl:px-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `xxl:py-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `xxl:pt-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `xxl:pb-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `xxl:pl-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `xxl:pr-[${i + 1}rem]`),
    //ultrawide
    ...Array.from({ length: 101 }, (_, i) => `ultrawide:p-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `ultrawide:px-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `ultrawide:py-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `ultrawide:pt-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `ultrawide:pb-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `ultrawide:pl-[${i + 1}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `ultrawide:pr-[${i + 1}rem]`),
    // Dynamic border classes
    ...Array.from({ length: 101 }, (_, i) => `border-[${i}px]`),
    ...Array.from({ length: 101 }, (_, i) => `border-t-[${i}px]`),
    ...Array.from({ length: 101 }, (_, i) => `border-b-[${i}px]`),
    ...Array.from({ length: 101 }, (_, i) => `border-l-[${i}px]`),
    ...Array.from({ length: 101 }, (_, i) => `border-r-[${i}px]`),
    // text size
    ...Array.from({ length: 101 }, (_, i) => `text-[${i}px]`),
    // text size for devices
    ...Array.from({ length: 101 }, (_, i) => `xs:text-[${i}px]`),
    ...Array.from({ length: 101 }, (_, i) => `mob:text-[${i}px]`),
    ...Array.from({ length: 101 }, (_, i) => `sm:text-[${i}px]`),
    ...Array.from({ length: 101 }, (_, i) => `md:text-[${i}px]`),
    ...Array.from({ length: 101 }, (_, i) => `lg:text-[${i}px]`),
    ...Array.from({ length: 101 }, (_, i) => `xl:text-[${i}px]`),
    ...Array.from({ length: 101 }, (_, i) => `xxl:text-[${i}px]`),
    ...Array.from({ length: 101 }, (_, i) => `ultrawide:text-[${i}px]`),
    // text size for rem
    ...Array.from({ length: 101 }, (_, i) => `text-[${i}rem]`),
    // text size for devices
    ...Array.from({ length: 101 }, (_, i) => `xs:text-[${i}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `mob:text-[${i}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `sm:text-[${i}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `md:text-[${i}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `lg:text-[${i}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `xl:text-[${i}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `xxl:text-[${i}rem]`),
    ...Array.from({ length: 101 }, (_, i) => `ultrawide:text-[${i}rem]`),
    // text size for em
    ...Array.from({ length: 101 }, (_, i) => `text-[${i}em]`),
    // text size for devices
    ...Array.from({ length: 101 }, (_, i) => `xs:text-[${i}em]`),
    ...Array.from({ length: 101 }, (_, i) => `mob:text-[${i}em]`),
    ...Array.from({ length: 101 }, (_, i) => `sm:text-[${i}em]`),
    ...Array.from({ length: 101 }, (_, i) => `md:text-[${i}em]`),
    ...Array.from({ length: 101 }, (_, i) => `lg:text-[${i}em]`),
    ...Array.from({ length: 101 }, (_, i) => `xl:text-[${i}em]`),
    ...Array.from({ length: 101 }, (_, i) => `xxl:text-[${i}em]`),
    ...Array.from({ length: 101 }, (_, i) => `ultrawide:text-[${i}em]`),
    // dynamic arbitrary color classes
    ...Array.from({ length: 101 }, (_, i) => `text-color-[${i}]`),
    ...Array.from({ length: 101 }, (_, i) => `bg-color-[${i}]`),
    // line height
    ...Array.from({ length: 101 }, (_, i) => `line-height-[${i}]`),
    // line height for devices
    ...Array.from({ length: 101 }, (_, i) => `xs:line-height-[${i}]`),
    ...Array.from({ length: 101 }, (_, i) => `mob:line-height-[${i}]`),
    ...Array.from({ length: 101 }, (_, i) => `sm:line-height-[${i}]`),
    ...Array.from({ length: 101 }, (_, i) => `md:line-height-[${i}]`),
    ...Array.from({ length: 101 }, (_, i) => `lg:line-height-[${i}]`),
    ...Array.from({ length: 101 }, (_, i) => `xl:line-height-[${i}]`),
    ...Array.from({ length: 101 }, (_, i) => `xxl:line-height-[${i}]`),
    ...Array.from({ length: 101 }, (_, i) => `ultrawide:line-height-[${i}]`),
  ],
};
