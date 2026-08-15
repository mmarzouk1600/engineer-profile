import { defineStore } from 'pinia'

export const useStatisticsNavbarStore = defineStore('statistics-navbar', {
    state: () => ({
        count: 0
    }),
    actions: {
        increment() {
            this.count++
        }
    }
})
