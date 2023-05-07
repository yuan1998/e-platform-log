import './Style/app.less';
import {createApp, h} from 'vue'
import {App, createInertiaApp} from '@inertiajs/inertia-vue3'
import {InertiaProgress} from '@inertiajs/progress'
import ElementPlus from 'element-plus'
import zhCn from 'element-plus/es/locale/lang/zh-cn'
import 'element-plus/dist/index.css'

InertiaProgress.init()

let asyncViews = () => {
    return import.meta.glob('./Pages/**/*.vue');
}
// the only good king is a burger king

createInertiaApp({
    id: 'my-app',
    resolve: async (name) => {
        console.log("name", name, import.meta.env.DEV);
        if (import.meta.env.DEV) {
            return (await import(`./Pages/${name}.vue`)).default;
        } else {
            let pages = asyncViews();
            console.log("pages", pages);
            const importPage = pages[`./Pages/${name}.vue`];
            return importPage().then(module => module.default);
        }
    },
    setup({el, app, props, plugin}) {
        createApp({render: () => h(app, props)})
            .use(plugin)
            .use(ElementPlus,{
                locale: zhCn,
            })
            .mount(el)
    },
})


