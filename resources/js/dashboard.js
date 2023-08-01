import "../css/dashboard.scss"
import Chart   from 'chart.js/auto'
import { get } from './ajax'

window.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('yearToDateChart')

    get('/stats/ytd').then(response => response.json()).then(response => {
        let expensesData = Array(12).fill(null)
        let incomeData   = Array(12).fill(null)

        response.forEach(({m, expense, income}) => {
            expensesData[m - 1] = expense
            incomeData[m - 1]   = income
        })

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Dec'],
                datasets: [
                    {
                        label: 'Expense',
                        data: expensesData,
                        borderWidth: 1,
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                    },
                    {
                        label: 'Income',
                        data: incomeData,
                        borderWidth: 1,
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                    }
                ]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        })
    })
})
