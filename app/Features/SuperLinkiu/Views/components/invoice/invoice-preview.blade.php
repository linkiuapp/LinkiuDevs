@extends('shared::layouts.admin')

@section('title', 'Vista Previa de Factura')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h2 class="title-card">Vista Previa de Factura</h2>
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" class="bg-primary-200 text-white-50 px-4 py-2 rounded-lg inline-flex items-center gap-2 hover:bg-primary-300 transition-colors">
                    <x-solar-letter-outline class="w-5 h-5" />
                    <span class="text-base">Enviar Factura</span>
                </button>
                <button type="button" class="bg-warning-200 text-black-500 px-4 py-2 rounded-lg inline-flex items-center gap-2 hover:bg-warning-400 transition-colors">
                    <x-solar-download-outline class="w-5 h-5" />
                    <span class="text-base">Descargar</span>
                </button>
                <button type="button" class="bg-success-200 text-black-300 px-4 py-2 rounded-lg inline-flex items-center gap-2 hover:bg-success-300 transition-colors">
                    <x-solar-pen-outline class="w-5 h-5" />
                    <span class="text-base">Editar</span>
                </button>
                <button type="button" onclick="printInvoice()" class="bg-info-200 text-white-50 px-4 py-2 rounded-lg inline-flex items-center gap-2 hover:bg-info-300 transition-colors">
                    <x-solar-printer-outline class="w-5 h-5" />
                    <span class="text-base">Imprimir</span>
                </button>
            </div>
        </div>
    </div>

    <div class="p-6">
        <div class="card-body" id="invoice">
            <div class="max-w-4xl mx-auto">
                <div class="card-body">
                    <!-- Header de la factura -->
                    <div class="card-body">
                        <div class="flex flex-wrap justify-between gap-4">
                            <div>
                                <h3 class="text-xl font-semibold text-black-500 mb-2">Factura #3492</h3>
                                <p class="mb-1 text-base text-black-400">Fecha de Emisión: 25/08/2020</p>
                                <p class="mb-0 text-base text-black-400">Fecha de Vencimiento: 29/08/2020</p>
                            </div>
                            <div class="text-right flex flex-col items-end">
                                <img src="{{ asset('assets/images/Logo_Linkiu.svg') }}" alt="Logo Linkiu" class="mb-2 h-12">
                                <p class="mb-1 text-sm text-black-400">4517 Washington Ave. Manchester, Kentucky 39495</p>
                                <p class="mb-0 text-sm text-black-400">random@gmail.com, +1 543 2198</p>
                            </div>
                        </div>
                    </div>

                    <!-- Información del cliente -->
                    <div class="py-6 px-6">
                        <div class="flex flex-wrap justify-between gap-6">
                            <div>
                                <h4 class="text-xl font-semibold text-black-500 mb-4">Facturar A:</h4>
                                <table class="text-base text-black-400">
                                    <tbody>
                                        <tr>
                                            <td class="py-1">Nombre</td>
                                            <td class="ps-2 py-1">: Will Marthas</td>
                                        </tr>
                                        <tr>
                                            <td class="py-1">Dirección</td>
                                            <td class="ps-2 py-1">: 4517 Washington Ave. USA</td>
                                        </tr>
                                        <tr>
                                            <td class="py-1">Teléfono</td>
                                            <td class="ps-2 py-1">: +1 543 2198</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div>
                                <table class="text-base text-black-400">
                                    <tbody>
                                        <tr>
                                            <td class="py-1">Fecha de Emisión</td>
                                            <td class="ps-2 py-1">: 25 Jan 2025</td>
                                        </tr>
                                        <tr>
                                            <td class="py-1">ID de Orden</td>
                                            <td class="ps-2 py-1">: #653214</td>
                                        </tr>
                                        <tr>
                                            <td class="py-1">ID de Envío</td>
                                            <td class="ps-2 py-1">: #965215</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Tabla de items -->
                        <div class="mt-8">
                            <div class="overflow-x-auto">
                                <table class="w-full border-collapse border border-white-200">
                                    <thead>
                                        <tr class="bg-white-100">
                                            <th class="border border-white-200 px-4 py-3 text-left text-base text-black-500">No.</th>
                                            <th class="border border-white-200 px-4 py-3 text-left text-base text-black-500">Artículos</th>
                                            <th class="border border-white-200 px-4 py-3 text-left text-base text-black-500">Cantidad</th>
                                            <th class="border border-white-200 px-4 py-3 text-left text-base text-black-500">Unidades</th>
                                            <th class="border border-white-200 px-4 py-3 text-left text-base text-black-500">Precio Unit.</th>
                                            <th class="border border-white-200 px-4 py-3 text-right text-base text-black-500">Precio</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="border border-white-200 px-4 py-3 text-base text-black-400">01</td>
                                            <td class="border border-white-200 px-4 py-3 text-base text-black-400">Apple's Shoes</td>
                                            <td class="border border-white-200 px-4 py-3 text-base text-black-400">5</td>
                                            <td class="border border-white-200 px-4 py-3 text-base text-black-400">PC</td>
                                            <td class="border border-white-200 px-4 py-3 text-base text-black-400">$200</td>
                                            <td class="border border-white-200 px-4 py-3 text-base text-black-400 text-right">$1000.00</td>
                                        </tr>
                                        <tr>
                                            <td class="border border-white-200 px-4 py-3 text-base text-black-400">02</td>
                                            <td class="border border-white-200 px-4 py-3 text-base text-black-400">Apple's Shoes</td>
                                            <td class="border border-white-200 px-4 py-3 text-base text-black-400">5</td>
                                            <td class="border border-white-200 px-4 py-3 text-base text-black-400">PC</td>
                                            <td class="border border-white-200 px-4 py-3 text-base text-black-400">$200</td>
                                            <td class="border border-white-200 px-4 py-3 text-base text-black-400 text-right">$1000.00</td>
                                        </tr>
                                        <tr>
                                            <td class="border border-white-200 px-4 py-3 text-base text-black-400">03</td>
                                            <td class="border border-white-200 px-4 py-3 text-base text-black-400">Apple's Shoes</td>
                                            <td class="border border-white-200 px-4 py-3 text-base text-black-400">5</td>
                                            <td class="border border-white-200 px-4 py-3 text-base text-black-400">PC</td>
                                            <td class="border border-white-200 px-4 py-3 text-base text-black-400">$200</td>
                                            <td class="border border-white-200 px-4 py-3 text-base text-black-400 text-right">$1000.00</td>
                                        </tr>
                                        <tr>
                                            <td class="border border-white-200 px-4 py-3 text-base text-black-400">04</td>
                                            <td class="border border-white-200 px-4 py-3 text-base text-black-400">Apple's Shoes</td>
                                            <td class="border border-white-200 px-4 py-3 text-base text-black-400">5</td>
                                            <td class="border border-white-200 px-4 py-3 text-base text-black-400">PC</td>
                                            <td class="border border-white-200 px-4 py-3 text-base text-black-400">$200</td>
                                            <td class="border border-white-200 px-4 py-3 text-base text-black-400 text-right">$1000.00</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Totales -->
                            <div class="flex flex-wrap justify-between gap-6 mt-8">
                                <div>
                                    <p class="text-base mb-2">
                                        <span class="text-black-500 font-semibold">Vendido por:</span> Jammal
                                    </p>
                                    <p class="text-base mb-0 text-black-400">Gracias por tu compra</p>
                                </div>
                                <div>
                                    <table class="text-base">
                                        <tbody>
                                            <tr>
                                                <td class="pr-16 py-1">Subtotal:</td>
                                                <td class="pl-6 py-1">
                                                    <span class="text-black-500 font-semibold">$4000.00</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="pr-16 py-1">Descuento:</td>
                                                <td class="pl-6 py-1">
                                                    <span class="text-black-500 font-semibold">$0.00</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="pr-16 py-1 border-b border-white-200 pb-4">Impuesto:</td>
                                                <td class="pl-6 py-1 border-b border-white-200 pb-4">
                                                    <span class="text-black-500 font-semibold">$0.00</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="pr-16 pt-4">
                                                    <span class="text-black-500 font-semibold">Total:</span>
                                                </td>
                                                <td class="pl-6 pt-4">
                                                    <span class="text-black-500 font-semibold">$4000.00</span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Mensaje de agradecimiento -->
                        <div class="mt-16">
                            <p class="text-center text-black-400 text-base font-semibold">¡Gracias por tu compra!</p>
                        </div>

                        <!-- Firmas -->
                        <div class="flex flex-wrap justify-between items-end mt-16">
                            <div class="text-base border-t border-white-200 inline-block px-3 pt-2">Firma del Cliente</div>
                            <div class="text-base border-t border-white-200 inline-block px-3 pt-2">Firma del Autorizado</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function printInvoice() {
    var printContents = document.getElementById("invoice").innerHTML;
    var originalContents = document.body.innerHTML;

    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
}
</script>
@endsection 