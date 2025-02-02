import { OAuth2Client } from "google-auth-library";

export default defineEventHandler(async (event) => {
  const body = await readBody(event);
  const token = body.token;

  if (!token) {
    throw createError({
      statusCode: 400,
      message: "Token is required",
    });
  }

  const config = useRuntimeConfig();
  const client = new OAuth2Client(config.googleClientId);

  try {
    const ticket = await client.verifyIdToken({
      idToken: token,
      audience: config.googleClientId,
    });
    const payload = ticket.getPayload();

    if (!payload) {
      throw createError({
        statusCode: 401,
        message: "Invalid Google token",
      });
    }

    // Preparar datos para Laravel
    const userData = {
      name: payload.given_name || '',
      last_name: payload.family_name || '',
      email: payload.email || '',
      google_id: payload.sub,
      image: payload.picture || '',
    };

    // Enviar a Laravel
    const laravelResponse = await $fetch(`${useRuntimeConfig().public.apiBaseUrl}/api/google-login`, {
      method: 'POST',
      body: userData,
    });

    return laravelResponse;
  } catch (error) {
    console.error('Google Login Error:', error);
    throw createError({
      statusCode: 500,
      message: "Google login verification failed",
      cause: error,
    });
  }
});