import { FormEvent, useState } from "react";
import { useMutation } from "@tanstack/react-query";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { Button } from "@/components/ui/button";
import { useToast } from "@/hooks/use-toast";
import { apiRequest } from "@/lib/queryClient";

type ContactPayload = {
  name: string;
  email: string;
  message: string;
};

export function FooterContactForm() {
  const { toast } = useToast();
  const [form, setForm] = useState<ContactPayload>({
    name: "",
    email: "",
    message: "",
  });

  const mutation = useMutation({
    mutationFn: async (payload: ContactPayload) => {
      const response = await apiRequest("POST", "/api/contact", payload);
      return response.json();
    },
    onSuccess: () => {
      setForm({ name: "", email: "", message: "" });
      toast({
        title: "Message sent",
        description: "Support will get back to you shortly.",
      });
    },
    onError: (error: Error) => {
      toast({
        title: "Unable to send message",
        description: error.message,
        variant: "destructive",
      });
    },
  });

  const handleSubmit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    mutation.mutate({
      name: form.name.trim(),
      email: form.email.trim(),
      message: form.message.trim(),
    });
  };

  return (
    <form className="space-y-2" onSubmit={handleSubmit} data-testid="form-footer-contact">
      <Input
        placeholder="Your name"
        value={form.name}
        onChange={(event) => setForm((current) => ({ ...current, name: event.target.value }))}
        data-testid="input-contact-name"
      />
      <Input
        type="email"
        placeholder="you@example.com"
        value={form.email}
        onChange={(event) => setForm((current) => ({ ...current, email: event.target.value }))}
        data-testid="input-contact-email"
      />
      <Textarea
        placeholder="How can we help?"
        value={form.message}
        onChange={(event) => setForm((current) => ({ ...current, message: event.target.value }))}
        className="min-h-[88px]"
        data-testid="textarea-contact-message"
      />
      <Button
        type="submit"
        className="w-full"
        disabled={mutation.isPending}
        data-testid="button-contact-submit"
      >
        {mutation.isPending ? "Sending..." : "Send message"}
      </Button>
    </form>
  );
}
