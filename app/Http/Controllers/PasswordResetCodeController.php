<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResetPasswordWithCodeRequest;
use App\Http\Requests\SendResetCodeRequest;
use App\Http\Requests\VerifyResetCodeRequest;
use App\Models\User;
use App\Notifications\PasswordResetCodeNotification;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetCodeController extends Controller
{
    /**
     * Expiração do código em minutos.
     */
    private const CODE_EXPIRATION_MINUTES = 15;

    /**
     * Exibe o formulário de solicitação de código.
     */
    public function showForgotPasswordForm(): Response
    {
        return Inertia::render('public/auth/forgot-password');
    }

    /**
     * Envia o código de verificação por e-mail.
     */
    public function sendCode(SendResetCodeRequest $request): RedirectResponse
    {
        $email = $request->validated('email');

        // Gera código de 6 dígitos
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Remove códigos anteriores do mesmo e-mail
        DB::table('password_reset_codes')
            ->where('email', $email)
            ->delete();

        // Insere novo código
        DB::table('password_reset_codes')->insert([
            'email' => $email,
            'code' => Hash::make($code),
            'verified' => false,
            'expires_at' => now()->addMinutes(self::CODE_EXPIRATION_MINUTES),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Envia notificação
        $user = User::where('email', $email)->first();
        $user->notify(new PasswordResetCodeNotification($code));

        return redirect()
            ->route('password.verify-code')
            ->with('email', $email)
            ->with('status', 'Enviamos um código de verificação para seu e-mail.');
    }

    /**
     * Exibe o formulário de verificação do código.
     */
    public function showVerifyCodeForm(): Response
    {
        return Inertia::render('public/auth/verify-code', [
            'email' => session('email', ''),
        ]);
    }

    /**
     * Verifica o código informado.
     */
    public function verifyCode(VerifyResetCodeRequest $request): RedirectResponse
    {
        $email = $request->validated('email');
        $code = $request->validated('code');

        $resetCode = DB::table('password_reset_codes')
            ->where('email', $email)
            ->where('verified', false)
            ->first();

        if (! $resetCode) {
            return back()->withErrors([
                'code' => 'Código inválido ou expirado. Solicite um novo código.',
            ]);
        }

        // Verifica expiração
        if (now()->greaterThan($resetCode->expires_at)) {
            DB::table('password_reset_codes')
                ->where('email', $email)
                ->delete();

            return back()->withErrors([
                'code' => 'Código expirado. Solicite um novo código.',
            ]);
        }

        // Verifica se o código está correto
        if (! Hash::check($code, $resetCode->code)) {
            return back()->withErrors([
                'code' => 'Código incorreto. Verifique e tente novamente.',
            ]);
        }

        // Marca como verificado
        DB::table('password_reset_codes')
            ->where('email', $email)
            ->update(['verified' => true, 'updated_at' => now()]);

        return redirect()
            ->route('password.reset-form')
            ->with('email', $email)
            ->with('code', $code)
            ->with('status', 'Código verificado! Defina sua nova senha.');
    }

    /**
     * Exibe o formulário de redefinição de senha.
     */
    public function showResetPasswordForm(): Response
    {
        return Inertia::render('public/auth/reset-password', [
            'email' => session('email', ''),
            'code' => session('code', ''),
        ]);
    }

    /**
     * Redefine a senha do usuário.
     */
    public function resetPassword(ResetPasswordWithCodeRequest $request): RedirectResponse
    {
        $email = $request->validated('email');
        $code = $request->validated('code');
        $password = $request->validated('password');

        $resetCode = DB::table('password_reset_codes')
            ->where('email', $email)
            ->where('verified', true)
            ->first();

        if (! $resetCode) {
            return back()->withErrors([
                'code' => 'Código inválido ou não verificado. Reinicie o processo.',
            ]);
        }

        // Verifica expiração (extra 5 minutos após verificação)
        if (now()->greaterThan($resetCode->expires_at)) {
            DB::table('password_reset_codes')
                ->where('email', $email)
                ->delete();

            return back()->withErrors([
                'code' => 'Sessão expirada. Solicite um novo código.',
            ]);
        }

        // Verifica código novamente
        if (! Hash::check($code, $resetCode->code)) {
            return back()->withErrors([
                'code' => 'Código inválido. Reinicie o processo.',
            ]);
        }

        // Atualiza a senha
        $user = User::where('email', $email)->first();
        $user->forceFill([
            'password' => Hash::make($password),
            'remember_token' => Str::random(60),
        ])->save();

        // Remove código usado
        DB::table('password_reset_codes')
            ->where('email', $email)
            ->delete();

        // Dispara evento
        event(new PasswordReset($user));

        return redirect()
            ->route('login')
            ->with('status', 'Senha redefinida com sucesso! Faça login com sua nova senha.');
    }

    /**
     * Reenvia o código de verificação.
     */
    public function resendCode(SendResetCodeRequest $request): RedirectResponse
    {
        return $this->sendCode($request);
    }
}
