"use client";

import { useState } from "react";
import { 
  Search, 
  Plus, 
  Minus, 
  Trash2, 
  User, 
  CreditCard, 
  Banknote,
  ShoppingCart,
  ChevronRight,
  ChevronLeft
} from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Separator } from "@/components/ui/separator";

// Mock data for products in van
const vanProducts = [
  { id: "1", name: "Premium Basmati Rice 5kg", price: 15.50, stock: 24, unit: "Bag" },
  { id: "2", name: "Sunflower Oil 1L", price: 4.25, stock: 12, unit: "Bottle" },
  { id: "3", name: "Organic Honey 500g", price: 12.00, stock: 8, unit: "Jar" },
];

export default function MobileInvoicePage() {
  const [step, setStep] = useState(1); // 1: Select Items, 2: Customer & Payment
  const [cart, setCart] = useState<any[]>([]);
  const [paymentType, setPaymentType] = useState<"cash" | "credit">("cash");

  const addToCart = (product: any) => {
    const existing = cart.find(item => item.id === product.id);
    if (existing) {
      if (existing.quantity < product.stock) {
        setCart(cart.map(item => 
          item.id === product.id ? { ...item, quantity: item.quantity + 1 } : item
        ));
      }
    } else {
      setCart([...cart, { ...product, quantity: 1 }]);
    }
  };

  const removeFromCart = (productId: string) => {
    const existing = cart.find(item => item.id === productId);
    if (existing.quantity > 1) {
      setCart(cart.map(item => 
        item.id === productId ? { ...item, quantity: item.quantity - 1 } : item
      ));
    } else {
      setCart(cart.filter(item => item.id !== productId));
    }
  };

  const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);

  return (
    <div className="max-w-md mx-auto pb-20">
      <div className="flex items-center justify-between mb-4">
        <h1 className="text-xl font-bold">New Sale</h1>
        <Badge variant="outline" className="bg-primary/10 text-primary">Van: V-001</Badge>
      </div>

      {step === 1 ? (
        <div className="space-y-4">
          <div className="relative">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
            <Input placeholder="Search products..." className="pl-10" />
          </div>

          <div className="space-y-2">
            {vanProducts.map(product => (
              <Card key={product.id} className="overflow-hidden">
                <CardContent className="p-3 flex items-center justify-between">
                  <div className="flex-1">
                    <p className="font-medium text-sm">{product.name}</p>
                    <p className="text-xs text-slate-500">${product.price.toFixed(2)} | Stock: {product.stock}</p>
                  </div>
                  <div className="flex items-center gap-2">
                    {cart.find(i => i.id === product.id) ? (
                      <div className="flex items-center gap-3 bg-slate-100 rounded-full px-2 py-1">
                        <button onClick={() => removeFromCart(product.id)} className="p-1 text-slate-500"><Minus className="h-4 w-4" /></button>
                        <span className="text-sm font-bold">{cart.find(i => i.id === product.id).quantity}</span>
                        <button onClick={() => addToCart(product)} className="p-1 text-primary"><Plus className="h-4 w-4" /></button>
                      </div>
                    ) : (
                      <Button size="sm" onClick={() => addToCart(product)}>Add</Button>
                    )}
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        </div>
      ) : (
        <div className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle className="text-sm">Customer Details</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="flex items-center gap-3 p-3 rounded-lg border bg-slate-50">
                <User className="h-5 w-5 text-slate-400" />
                <div className="flex-1">
                  <p className="text-sm font-medium">Select Customer</p>
                  <p className="text-xs text-slate-500">Tap to search...</p>
                </div>
                <ChevronRight className="h-4 w-4 text-slate-400" />
              </div>

              <div className="grid grid-cols-2 gap-3">
                <button 
                  onClick={() => setPaymentType("cash")}
                  className={cn(
                    "flex flex-col items-center gap-2 p-4 rounded-xl border-2 transition-all",
                    paymentType === "cash" ? "border-primary bg-primary/5 text-primary" : "border-slate-200 text-slate-500"
                  )}
                >
                  <Banknote className="h-6 w-6" />
                  <span className="text-xs font-bold">Cash Sale</span>
                </button>
                <button 
                  onClick={() => setPaymentType("credit")}
                  className={cn(
                    "flex flex-col items-center gap-2 p-4 rounded-xl border-2 transition-all",
                    paymentType === "credit" ? "border-primary bg-primary/5 text-primary" : "border-slate-200 text-slate-500"
                  )}
                >
                  <CreditCard className="h-6 w-6" />
                  <span className="text-xs font-bold">Credit Sale</span>
                </button>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle className="text-sm">Order Summary</CardTitle>
            </CardHeader>
            <CardContent className="space-y-2">
              {cart.map(item => (
                <div key={item.id} className="flex justify-between text-sm">
                  <span>{item.quantity}x {item.name}</span>
                  <span>${(item.price * item.quantity).toFixed(2)}</span>
                </div>
              ))}
              <Separator className="my-2" />
              <div className="flex justify-between font-bold text-lg">
                <span>Total</span>
                <span>${total.toFixed(2)}</span>
              </div>
            </CardContent>
          </Card>
        </div>
      )}

      {/* Sticky Bottom Bar */}
      <div className="fixed bottom-0 left-0 right-0 p-4 bg-white border-t flex items-center gap-4 max-w-md mx-auto">
        {step === 1 ? (
          <>
            <div className="flex-1">
              <p className="text-xs text-slate-500">Total Items: {cart.length}</p>
              <p className="text-lg font-bold">${total.toFixed(2)}</p>
            </div>
            <Button className="flex-1 h-12 rounded-xl" disabled={cart.length === 0} onClick={() => setStep(2)}>
              Next Step <ChevronRight className="ml-2 h-4 w-4" />
            </Button>
          </>
        ) : (
          <>
            <Button variant="outline" className="h-12 w-12 rounded-xl p-0" onClick={() => setStep(1)}>
              <ChevronLeft className="h-5 w-5" />
            </Button>
            <Button className="flex-1 h-12 rounded-xl text-lg font-bold">
              Complete Sale
            </Button>
          </>
        )}
      </div>
    </div>
  );
}

function cn(...classes: any[]) {
  return classes.filter(Boolean).join(" ");
}
