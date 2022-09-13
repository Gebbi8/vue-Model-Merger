import { createRouter, createWebHistory } from "vue-router";
import SimpleMerge from "../components/SimpleMerge.vue";

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: "/",
      name: "simple",
      component: SimpleMerge,
    },
    {
      path: "/user",
      name: "UserMerge",
      // route level code-splitting
      // this generates a separate chunk (About.[hash].js) for this route
      // which is lazy-loaded when the route is visited.
      component: () => import("../components/UserMerge.vue"),
    },
  ],
});

export default router;
