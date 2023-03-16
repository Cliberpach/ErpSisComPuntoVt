export const FormatNumber = (valor, digitos, style = false, pais = 'PE') => {
     let format = new Intl.NumberFormat(Pais(pais).format, {
          currency: Pais(pais).moneda,
          minimumFractionDigits: digitos,
          maximumFractionDigits: digitos,
          style: style ? 'currency' : undefined
     }).format(isNaN(Number(valor)) ? 0 : Number(valor));
     return format.replace(",", "");
}
export const configDatePicker = (fecha) => {
     let config = {
          singleDatePicker: true,
          autoApply: true,
          linkedCalendars: false,
          autoUpdateInput: false,
          showCustomRangeLabel: false,
          startDate: fecha,
          endDate: fecha,
          locale: {
               "format": "DD/MM/YYYY",
               "separator": " / ",
               "applyLabel": "Aplicar",
               "cancelLabel": "Cancelar",
               "fromLabel": "From",
               "toLabel": "To",
               "customRangeLabel": "Custom",
               "weekLabel": "W",
               "daysOfWeek": [
                    "Lu",
                    "Ma",
                    "Mi",
                    "Ju",
                    "Vi",
                    "Sa",
                    "Do"
               ],
               "monthNames": [
                    "Enero",
                    "Febrero",
                    "Marzo",
                    "Abril",
                    "Mayo",
                    "Junio",
                    "Julio",
                    "Agosto",
                    "Septiembre",
                    "Octubre",
                    "Noviembre",
                    "Diciembre"
               ],
               "firstDay": 1
          }
     };
     return config;
}
function Pais(pais) {
     switch (pais) {
          case 'PE': {
               return {
                    format: 'es-PE',
                    moneda: 'PEN'
               }
          }
          case 'US': {
               return {
                    format: 'en-US',
                    moneda: 'USD'
               }
          }
     }
}