import path from 'path'

const nextConfig = {
  reactStrictMode: true,
  images: {
    disableStaticImages: true,
  },
  sassOptions: {
    includePaths: [path.join(process.cwd(), 'src', 'styles')],
    silenceDeprecations: ['legacy-js-api'],
  },
  webpack(config) {
    config.module.rules.push({
      test: /\.(png|jpe?g|gif|webp|avif|svg|woff2?)$/i,
      type: 'asset/resource',
      generator: {
        filename: 'static/assets/[name].[contenthash][ext]',
      },
    })

    return config
  },
}

export default nextConfig
