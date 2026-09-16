import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { useNavigate } from 'react-router-dom'
import toast from 'react-hot-toast'

import { api, apiError, tokenStore } from '../lib/api'
import type { User } from '../lib/types'

export function useAuth() {
  const queryClient = useQueryClient()
  const navigate = useNavigate()

  const me = useQuery({
    queryKey: ['me'],
    queryFn: async () => (tokenStore.get() ? (await api.get<{ user: User }>('/auth/me')).data.user : null),
    retry: false,
  })

  const login = useMutation({
    mutationFn: async (payload: { email: string; password: string }) =>
      (await api.post<{ token: string; user: User }>('/auth/login', payload)).data,
    onSuccess: (data) => {
      tokenStore.set(data.token)
      queryClient.setQueryData(['me'], data.user)
      toast.success(`Selamat datang, ${data.user.name}!`)
      navigate('/', { replace: true })
    },
    onError: (err) => toast.error(apiError(err)),
  })

  const register = useMutation({
    mutationFn: async (payload: { name: string; email: string; password: string }) =>
      (
        await api.post<{ token: string; user: User }>('/auth/register', {
          ...payload,
          password_confirmation: payload.password,
        })
      ).data,
    onSuccess: (data) => {
      tokenStore.set(data.token)
      queryClient.setQueryData(['me'], data.user)
      toast.success('Akun berhasil dibuat')
      navigate('/', { replace: true })
    },
    onError: (err) => toast.error(apiError(err)),
  })

  const logout = useMutation({
    mutationFn: async () => {
      if (tokenStore.get()) await api.post('/auth/logout')
    },
    onSuccess: () => {
      tokenStore.set(null)
      queryClient.clear()
      toast.success('Berhasil keluar')
      navigate('/login', { replace: true })
    },
    onError: () => {
      tokenStore.set(null)
      queryClient.clear()
      navigate('/login', { replace: true })
    },
  })

  const forgotPassword = useMutation({
    mutationFn: async (payload: { email: string }) =>
      (await api.post<{ message: string }>('/auth/forgot-password', payload)).data,
    onError: (err) => toast.error(apiError(err)),
  })

  const resetPassword = useMutation({
    mutationFn: async (payload: { token: string; email: string; password: string }) =>
      (await api.post<{ message: string }>('/auth/reset-password', {
        ...payload,
        password_confirmation: payload.password,
      })).data,
    onError: (err) => toast.error(apiError(err)),
  })

  const updateProfile = useMutation({
    mutationFn: async (payload: { name: string; phone?: string | null }) =>
      (await api.put<{ user: User }>('/user/profile', payload)).data.user,
    onSuccess: (data) => {
      queryClient.setQueryData(['me'], data)
      toast.success('Profil berhasil diperbarui')
    },
    onError: (err) => toast.error(apiError(err)),
  })

  const updatePassword = useMutation({
    mutationFn: async (payload: { current_password: string; password: string }) =>
      (await api.put<{ message: string }>('/user/password', {
        ...payload,
        password_confirmation: payload.password,
      })).data,
    onSuccess: () => toast.success('Kata sandi berhasil diperbarui'),
    onError: (err) => toast.error(apiError(err)),
  })

  const resendVerification = useMutation({
    mutationFn: async () => (await api.post<{ message: string }>('/auth/email/resend')).data,
    onSuccess: (data) => toast.success(data.message),
    onError: (err) => toast.error(apiError(err)),
  })

  const user = me.data ?? null
  return {
    user,
    loading: me.isLoading,
    isAdmin: user?.role === 'admin',
    login,
    register,
    logout,
    forgotPassword,
    resetPassword,
    updateProfile,
    updatePassword,
    resendVerification,
  }
}